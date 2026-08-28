<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

/**
 * The books a bulk action is about to touch, listed so one can be dropped
 * before anything is written. The state is the ids that are still in.
 */
class SelectedBooksList extends Field
{
    protected string $view = 'filament.forms.components.selected-books-list';

    /** @var array<int, array<string, mixed>> | Closure */
    protected array | Closure $books = [];

    /** @param array<int, array<string, mixed>> | Closure $books */
    public function books(array | Closure $books): static
    {
        $this->books = $books;

        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function getBooks(): array
    {
        return $this->evaluate($this->books);
    }
}
