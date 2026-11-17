<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Actions\PlaceOrder;
use App\Models\Governorate;
use App\Models\StorePage;
use App\Services\CalculateCart;
use App\Services\StoreImage;
use App\Services\StoreSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Checkout extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $phoneCountry = 'EG';

    public string $phone = '';

    public ?int $governorateId = null;

    public string $address = '';

    public string $paymentMethod = 'cod';

    public mixed $paymentProof = null;

    public bool $acceptedPolicies = false;

    public string $idempotencyKey = '';

    public ?string $placedOrderNumber = null;

    public function mount(): void
    {
        $this->idempotencyKey = (string) Str::uuid();
        $this->governorateId = Governorate::query()->where('is_active', true)->where('is_default', true)->value('id');
    }

    public function placeOrder(PlaceOrder $placeOrder): void
    {
        $freeForAll = (bool) app(StoreSettings::class)->get('store.shipping.free_for_all', false);
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phoneCountry' => ['required', 'in:EG'],
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'governorateId' => $freeForAll ? ['nullable'] : ['required', 'integer', 'exists:governorates,id'],
            'address' => ['required', 'string', 'max:200'],
            'paymentMethod' => ['required', 'in:cod,instapay,vodafone_cash'],
            'paymentProof' => in_array($this->paymentMethod, ['instapay', 'vodafone_cash'], true)
                ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'] : ['nullable'],
            'acceptedPolicies' => ['accepted'],
        ]);

        $enabled = app(StoreSettings::class)->get('store.payment.enabled', []);
        if (! ($enabled[$this->paymentMethod] ?? false)) {
            throw ValidationException::withMessages(['paymentMethod' => __('store.payment_unavailable')]);
        }

        $proofPath = $this->paymentProof === null ? null : app(StoreImage::class)->savePrivate($this->paymentProof, 'payment-proofs');
        $digits = preg_replace('/\D+/', '', $this->phone) ?? '';
        $order = $placeOrder->handle([
            'name' => $this->name, 'phone_country' => $this->phoneCountry, 'phone' => '+20'.$digits,
            'governorate_id' => $this->governorateId, 'address' => $this->address,
            'payment_method' => $this->paymentMethod, 'payment_proof_path' => $proofPath,
            'idempotency_key' => $this->idempotencyKey,
        ], auth()->user());

        session()->forget('cart.items');
        $this->placedOrderNumber = $order->number;
        $this->dispatch('cart-updated', count: 0);
    }

    public function render(): View
    {
        $cart = app(CalculateCart::class)->calculate(session('cart.items', []));
        $freeForAll = (bool) app(StoreSettings::class)->get('store.shipping.free_for_all', false);
        $governorates = $freeForAll ? collect() : Governorate::query()->where('is_active', true)->orderBy('sort_order')->get();
        $selectedGovernorate = $this->governorateId === null ? null : Governorate::query()->find($this->governorateId);
        $shipping = $freeForAll || $cart['free_shipping'] ? 0.0 : ($selectedGovernorate === null ? 0.0 : (float) $selectedGovernorate->shipping_price);
        $payment = app(StoreSettings::class)->get('store.payment', []);
        $policies = StorePage::query()->whereIn('slug', ['terms', 'returns', 'shipping'])->get()->keyBy('slug');

        return view('livewire.storefront.checkout', compact('cart', 'governorates', 'freeForAll', 'shipping', 'payment', 'policies'))
            ->layout('layouts.store', ['title' => __('store.checkout')]);
    }
}
