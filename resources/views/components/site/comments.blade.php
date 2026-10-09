@props(['page', 'comments'])
<section id="comments" class="mt-16 border-t border-line pt-12" aria-labelledby="comments-heading">
    <h2 id="comments-heading" class="flex items-center gap-3 text-2xl font-bold text-secondary">Comments <span class="badge badge-primary text-sm">{{ $comments->count() }}</span></h2>

    @forelse ($comments as $comment)
        <article class="mt-5 flex gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary-dark font-display font-semibold text-white shadow-glow-primary">{{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}</div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <p class="font-semibold text-secondary">{{ $comment->name }}</p>
                    @if ($comment->rating)
                        <p class="flex text-accent" aria-label="Rated {{ $comment->rating }} out of 5">
                            @for ($i = 1; $i <= 5; $i++)<x-icon name="star" @class(['size-4', 'fill-current' => $i <= $comment->rating, 'opacity-30' => $i > $comment->rating]) />@endfor
                        </p>
                    @endif
                    <time class="text-xs text-slate-500" datetime="{{ $comment->created_at->toIso8601String() }}">{{ local_date($comment->created_at) }}</time>
                </div>
                <p class="mt-1.5 text-slate-700 whitespace-pre-line">{{ $comment->body }}</p>
            </div>
        </article>
    @empty
        <p class="mt-4 text-slate-500">No comments yet. Be the first to share your experience.</p>
    @endforelse

    <form action="{{ route('comments.store', $page) }}" method="POST" data-ajax-form data-reset class="mt-10 rounded-3xl border border-line bg-soft p-6 sm:p-8" novalidate>
        @csrf
        <h3 class="text-xl font-bold text-secondary">Leave a comment</h3>
        <p class="mt-1 text-sm text-slate-500">Your email is never published. Comments appear after moderation.</p>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <x-ui.input name="name" label="Name" required autocomplete="name" maxlength="120" />
            <x-ui.input name="email" type="email" label="Email" required autocomplete="email" />
        </div>
        <fieldset class="mt-4">
            <legend class="form-label">Rating <span class="font-normal text-slate-500">(optional)</span></legend>
            <div class="flex flex-row-reverse justify-end gap-1">
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" name="rating" value="{{ $i }}" id="rating-{{ $i }}" class="peer sr-only">
                    <label for="rating-{{ $i }}" class="cursor-pointer text-slate-300 transition peer-checked:text-accent hover:text-accent peer-hover:text-accent peer-focus-visible:ring-2 peer-focus-visible:ring-primary rounded" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">
                        <x-icon name="star" class="size-7 fill-current" /><span class="sr-only">{{ $i }} stars</span>
                    </label>
                @endfor
            </div>
        </fieldset>
        <x-ui.textarea name="body" label="Comment" rows="4" required class="mt-4" maxlength="2000" />
        <button class="btn btn-accent mt-6" data-loading-text="Submitting…">Post comment</button>
    </form>
</section>
