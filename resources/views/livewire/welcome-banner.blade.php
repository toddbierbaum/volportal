<div>
    @if ($userId && ! $sent)
        <div class="bg-fct-cyan-light/40 border-b border-fct-cyan/30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4 flex-wrap">
                <p class="text-sm text-fct-navy">
                    Welcome back, <strong>{{ $firstName }}</strong>.
                </p>
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" wire:click="sendLink" wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 bg-fct-navy rounded font-medium text-white hover:bg-fct-navy-light transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="sendLink">Send me a login link</span>
                        <span wire:loading wire:target="sendLink">Sending…</span>
                    </button>
                    <button type="button" wire:click="dismiss"
                            class="text-fct-navy/70 hover:text-fct-navy underline">
                        Not you?
                    </button>
                </div>
            </div>
        </div>
    @elseif ($sent)
        <div class="bg-green-50 border-b border-green-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-green-900 text-center">
                Check your email for a login link (valid 30 minutes).
            </div>
        </div>
    @endif
</div>
