<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class ProductImage extends Component
{
    public readonly ?string $imageUrl;

    public function __construct(
        public Product $product,
        public string $height = 'h-44'
    ) {
        $disk = Storage::disk(config('filesystems.media_disk'));
        $this->imageUrl = $product->image !== null
            && $disk->exists($product->image)
            ? $disk->url($product->image)
            : null;
    }

    public function render(): View
    {
        return view('components.product-image');
    }
}
