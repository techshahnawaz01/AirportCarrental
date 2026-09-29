<div data-repeater-item class="rounded-xl border border-line bg-surface-muted/50 p-4">
    <div class="mb-3 flex items-center justify-between">
        <span class="text-xs font-semibold text-fg-muted uppercase">Question <span data-repeater-number></span></span>
        <div class="flex gap-1">
            <button type="button" class="btn-icon size-7" data-repeater-move="up" aria-label="Move up"><x-icon name="arrow-up" class="size-4" /></button>
            <button type="button" class="btn-icon size-7" data-repeater-move="down" aria-label="Move down"><x-icon name="arrow-down" class="size-4" /></button>
            <button type="button" class="btn-icon size-7 hover:text-red-600" data-repeater-remove aria-label="Remove"><x-icon name="trash" class="size-4" /></button>
        </div>
    </div>
    <div class="space-y-3">
        <div>
            <input name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" class="form-control" placeholder="Question" aria-label="Question" maxlength="500">
            <p class="form-error" data-error-for="faqs.{{ $index }}.question" hidden></p>
        </div>
        <div>
            <textarea name="faqs[{{ $index }}][answer]" rows="3" class="form-control" placeholder="Answer" aria-label="Answer" maxlength="5000">{{ $faq['answer'] ?? '' }}</textarea>
            <p class="form-error" data-error-for="faqs.{{ $index }}.answer" hidden></p>
        </div>
    </div>
</div>
