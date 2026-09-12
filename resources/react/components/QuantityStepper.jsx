import { Minus, Plus } from 'lucide-react';

export default function QuantityStepper({ value, onChange, min = 1, max = null }) {
    const canDecrease = value > min;
    const canIncrease = max === null || value < max;

    return (
        <div className="inline-flex items-center rounded-full border border-white/15 bg-white/5">
            <button
                type="button"
                onClick={() => canDecrease && onChange(value - 1)}
                disabled={!canDecrease}
                aria-label="Disminuir cantidad"
                className="flex size-8 items-center justify-center rounded-full text-white/70 transition hover:text-white disabled:cursor-not-allowed disabled:opacity-30"
            >
                <Minus className="size-3.5" />
            </button>
            <span className="w-8 text-center text-sm font-semibold text-white" aria-live="polite">{value}</span>
            <button
                type="button"
                onClick={() => canIncrease && onChange(value + 1)}
                disabled={!canIncrease}
                aria-label="Aumentar cantidad"
                className="flex size-8 items-center justify-center rounded-full text-white/70 transition hover:text-white disabled:cursor-not-allowed disabled:opacity-30"
            >
                <Plus className="size-3.5" />
            </button>
        </div>
    );
}
