@php($hero = $page->meta('hero', []))
<x-ui.card title="Landing page hero" description="The title and summary above are used as the headline and sub-headline.">
    <div class="space-y-5">
        <x-ui.input name="data[hero][eyebrow]" label="Eyebrow text" :value="$hero['eyebrow'] ?? null" maxlength="80" help="Short label above the headline, e.g. “24/7 travel support”." />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input name="data[hero][primary_label]" label="Primary button label" :value="$hero['primary_label'] ?? null" maxlength="60" placeholder="Call now" />
            <x-ui.input name="data[hero][primary_url]" label="Primary button link" :value="$hero['primary_url'] ?? null" maxlength="500" placeholder="tel:+18335822357 or /contact-us" />
            <x-ui.input name="data[hero][secondary_label]" label="Secondary button label" :value="$hero['secondary_label'] ?? null" maxlength="60" placeholder="Learn more" />
            <x-ui.input name="data[hero][secondary_url]" label="Secondary button link" :value="$hero['secondary_url'] ?? null" maxlength="500" placeholder="#details or /parking" />
        </div>
        <p class="text-xs text-fg-muted">Build the rest of the page in Content with blocks such as <code>[media_text]</code>, <code>[link_cards]</code>, <code>[cta]</code> and <code>[contact_form]</code>.</p>
    </div>
</x-ui.card>
