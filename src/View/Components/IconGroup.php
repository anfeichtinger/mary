<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class IconGroup extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $hintClass = 'fieldset-label',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public Collection|array $options = new Collection(),

        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
    ) {
        $this->uuid = "mary" . md5(serialize($this)) . $id;
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }

    public function optionId(array $option = []): ?string
    {
        return $this->uuid . data_get($option, $this->optionValue);
    }

    public function prevOptionValue(int $index): ?string
    {
        $index--;
        return $index >= 0 ? $this->options[$index][$this->optionValue] : $this->options[count($this->options) - 1][$this->optionValue];
    }

    public function nextOptionValue(int $index): ?string
    {
        $index++;
        return $index < count($this->options) ? $this->options[$index][$this->optionValue] : $this->options[0][$this->optionValue];
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
                <div>
                    <fieldset class="fieldset py-0">
                        {{-- STANDARD LABEL --}}
                        @if($label)
                            <legend class="fieldset-legend mb-0.5">
                                {{ $label }}

                                @if($attributes->get('required'))
                                    <span class="text-error">*</span>
                                @endif
                            </legend>
                        @endif

                        <div class="join">
                            @foreach ($options as $index => $option)
                                <div>
                                    {{-- Hidden input for state --}}
                                    <input
                                        type="radio"
                                        id="{{ $optionId($option) }}"
                                        name="{{ $modelName() }}"
                                        value="{{ data_get($option, $optionValue) }}"
                                        aria-label="{{ data_get($option, $optionLabel) }}"
                                        @if(data_get($option, 'disabled')) disabled @endif

                                        {{ $attributes->whereStartsWith('wire:model') }}
                                        class="peer hidden" />

                                    {{-- The actual button --}}
                                    <label for="{{ $optionId($option) }}"
                                        {{
                                            $attributes->class([
                                                "join-item btn focus:relative peer-checked:btn-neutral",
                                                "!border-s-base-100 btn-disabled" => data_get($option, 'disabled')
                                            ])
                                        }}
                                        @if(!data_get($option, 'disabled')) tabindex="0" x-on:keydown.enter.prevent="$el.click()" x-on:keydown.space.prevent="$el.click()" @endif>

                                        {{-- Icon & Text --}}
                                        <x-mary-icon :name="data_get($option, 'icon')" :label="data_get($option, $optionLabel)"/>

                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- ERROR --}}
                        @if(!$omitError && $errors->has($errorFieldName()))
                            @foreach($errors->get($errorFieldName()) as $message)
                                @foreach(Arr::wrap($message) as $line)
                                    <div class="{{ $errorClass }}" x-class="text-error">{{ $line }}</div>
                                    @break($firstErrorOnly)
                                @endforeach
                                @break($firstErrorOnly)
                            @endforeach
                        @endif

                        {{-- HINT --}}
                        @if($hint)
                            <div class="{{ $hintClass }}" x-classes="fieldset-label">{{ $hint }}</div>
                        @endif
                    </fieldset>
                </div>
            BLADE;
    }
}
