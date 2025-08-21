<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FolderDetails extends Component
{

    public $title;
    public $items;
    /**
     * Create a new component instance.
     */
    public function __construct($title = '',$items = [])
    {
        $this->title = $title;
        $this->items = $items;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('app-components.folder-details');
    }
}
