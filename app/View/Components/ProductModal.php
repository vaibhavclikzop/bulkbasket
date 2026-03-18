<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $modalId;
    public $brand;
    public $category;
    public $product_uom;

    public function __construct($modalId = 'productModal', $brand = [],$category=[],$product_uom=[])
    {
       
        $this->modalId = $modalId;
        $this->brand = $brand;
        $this->category = $category;
        $this->product_uom=$product_uom;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.product-modal');
    }
}
