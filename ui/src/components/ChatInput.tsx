import { useState } from 'react';

type Props = {
  onSend: (message: string) => Promise<void>;
  disabled?: boolean;
};

export function ChatInput({ onSend, disabled = false }: Props) {
  const [value, setValue] = useState('');

  const send = async () => {
    if (!value.trim() || disabled) return;

    const message = value;
    setValue('');
    await onSend(message);
  };

  return (
    <div style={{ display: 'flex', gap: '8px', padding: '12px', borderTop: '1px solid #ddd' }}>
      <input
        value={value}
        onChange={e => setValue(e.target.value)}
        onKeyDown={e => e.key === 'Enter' && void send()}
        disabled={disabled}
        placeholder={disabled ? 'Waiting for response...' : 'Type your message...'}
        style={{ flex: 1, padding: '10px' }}
      />
      <button onClick={() => void send()} disabled={disabled || !value.trim()}>
        {disabled ? 'Sending...' : 'Send'}
      </button>
    </div>
  );
}
