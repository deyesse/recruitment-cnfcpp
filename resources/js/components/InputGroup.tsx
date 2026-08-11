import React, { useState, useEffect } from 'react';

interface InputGroupProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label: string;
    error?: string;
    fullWidth?: boolean;
    helperText?: string;
    digitsOnly?: boolean;
    decimalOnly?: boolean;
}

export const InputGroup: React.FC<InputGroupProps> = ({
    label,
    error,
    fullWidth = false,
    className = '',
    helperText,
    type,
    name = '',
    value = '',
    onChange,
    disabled = false,
    required = false,
    digitsOnly = false,
    decimalOnly = false,
    ...props
}) => {
    if (type === 'date') {
        return (
            <FormattedDateInput
                label={label}
                error={error}
                fullWidth={fullWidth}
                className={className}
                helperText={helperText}
                name={name}
                value={String(value || '')}
                onChange={onChange}
                disabled={disabled}
                required={required}
            />
        );
    }

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (digitsOnly) {
            e.target.value = e.target.value.replace(/\D/g, '');
        } else if (decimalOnly) {
            e.target.value = e.target.value.replace(/[^0-9.]/g, '');
            const parts = e.target.value.split('.');
            if (parts.length > 2) {
                e.target.value = parts[0] + '.' + parts.slice(1).join('');
            }
        }
        if (onChange) {
            onChange(e);
        }
    };

    return (
        <div className={`flex flex-col gap-2 mb-4 ${fullWidth ? 'w-full' : ''} ${className}`}>
            <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
                {label}
                {required && <span className="text-red-500">*</span>}
            </label>
            <input
                type={type}
                name={name}
                value={value}
                onChange={handleInputChange}
                disabled={disabled}
                required={required}
                className={`
          w-full px-4 py-2.5 rounded-lg border
          bg-white text-gray-900 placeholder-gray-400
          focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
          transition-all duration-200
          ${error ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'}
          disabled:bg-gray-100 disabled:text-gray-500
        `}
                {...props}
            />
            {helperText && <p className="text-xs text-gray-500">{helperText}</p>}
            {error && <span className="text-xs text-red-500 font-medium">{error}</span>}
        </div>
    );
};

interface FormattedDateInputProps {
    label: string;
    error?: string;
    fullWidth?: boolean;
    className?: string;
    helperText?: string;
    name: string;
    value: string; // ISO YYYY-MM-DD
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
    disabled?: boolean;
    required?: boolean;
}

const FormattedDateInput: React.FC<FormattedDateInputProps> = ({
    label,
    error,
    fullWidth = false,
    className = '',
    helperText,
    name,
    value,
    onChange,
    disabled = false,
    required = false,
}) => {
    // Convert YYYY-MM-DD to DD/MM/YYYY for display
    const isoToDisplay = (iso: string): string => {
        if (!iso) return '';
        const parts = iso.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return iso;
    };

    // Convert DD/MM/YYYY to YYYY-MM-DD for form state
    const displayToIso = (display: string): string => {
        const parts = display.split('/');
        if (parts.length === 3 && parts[2].length === 4) {
            const day = parts[0].padStart(2, '0');
            const month = parts[1].padStart(2, '0');
            const year = parts[2];
            return `${year}-${month}-${day}`;
        }
        return '';
    };

    const [displayVal, setDisplayVal] = useState<string>(() => isoToDisplay(value));

    useEffect(() => {
        setDisplayVal(isoToDisplay(value));
    }, [value]);

    const handleTextChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        let input = e.target.value.replace(/\D/g, ''); // digits only
        if (input.length > 8) input = input.slice(0, 8);

        let formatted = input;
        if (input.length > 2 && input.length <= 4) {
            formatted = `${input.slice(0, 2)}/${input.slice(2)}`;
        } else if (input.length > 4) {
            formatted = `${input.slice(0, 2)}/${input.slice(2, 4)}/${input.slice(4)}`;
        }

        setDisplayVal(formatted);

        const iso = displayToIso(formatted);
        if (onChange) {
            const syntheticEvent = {
                target: {
                    name,
                    value: iso,
                },
            } as unknown as React.ChangeEvent<HTMLInputElement>;
            onChange(syntheticEvent);
        }
    };

    const handleNativeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const isoVal = e.target.value;
        setDisplayVal(isoToDisplay(isoVal));
        if (onChange) {
            onChange(e);
        }
    };

    return (
        <div className={`flex flex-col gap-2 mb-4 ${fullWidth ? 'w-full' : ''} ${className}`}>
            <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
                {label}
                {required && <span className="text-red-500">*</span>}
            </label>
            <div className="relative flex items-center">
                <input type="hidden" name={name} value={value} />
                <input
                    type="text"
                    placeholder="dd/mm/yyyy"
                    value={displayVal}
                    onChange={handleTextChange}
                    maxLength={10}
                    disabled={disabled}
                    dir="ltr"
                    className={`
                        w-full px-4 py-2.5 rounded-lg border text-left font-mono tracking-widest
                        bg-white text-gray-900 placeholder-gray-400
                        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                        transition-all duration-200
                        ${error ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'}
                        disabled:bg-gray-100 disabled:text-gray-500
                    `}
                />
                <input
                    type="date"
                    id={`native-picker-${name}`}
                    value={value}
                    onChange={handleNativeChange}
                    disabled={disabled}
                    className="sr-only"
                />
                <button
                    type="button"
                    disabled={disabled}
                    onClick={() => {
                        const el = document.getElementById(`native-picker-${name}`) as HTMLInputElement;
                        if (el && typeof el.showPicker === 'function') {
                            el.showPicker();
                        } else if (el) {
                            el.focus();
                            el.click();
                        }
                    }}
                    className="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors p-1"
                    title="اختر التاريخ من التقويم"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
            {helperText && <p className="text-xs text-gray-500">{helperText}</p>}
            {error && <span className="text-xs text-red-500 font-medium">{error}</span>}
        </div>
    );
};

interface SelectGroupProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
    label: string;
    options: { value: string; label: string }[];
    error?: string;
    placeholder?: string;
}

export const SelectGroup: React.FC<SelectGroupProps> = ({
    label,
    options,
    className = '',
    error,
    placeholder = '-- اختر --',
    ...props
}) => {
    return (
        <div className={`flex flex-col gap-2 mb-4 w-full ${className}`}>
            <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
                {label}
                {props.required && <span className="text-red-500">*</span>}
            </label>
            <select
                className={`
          w-full px-4 py-2.5 rounded-lg border bg-white text-gray-900
          focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
          transition-all duration-200
          ${error ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'}
        `}
                {...props}
            >
                <option value="" disabled>{placeholder}</option>
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
            </select>
            {error && <span className="text-xs text-red-500 font-medium">{error}</span>}
        </div>
    );
};
