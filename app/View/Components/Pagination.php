<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Pagination\LengthAwarePaginator;

class Pagination extends Component
{
    public $paginator;
    public $wrapperClass;
    public $showInfo;

    /**
     * Create a new component instance.
     */
    public function __construct($paginator, $wrapperClass = 'mt-3', $showInfo = false)
    {
        $this->paginator = $paginator;
        $this->wrapperClass = $wrapperClass;
        $this->showInfo = $showInfo;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pagination');
    }
}
