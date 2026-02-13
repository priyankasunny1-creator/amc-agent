import { ChatMessage } from '../types/ChatTypes';

type Props = {
  messages: ChatMessage[];
  loading?: boolean;
  errorText?: string | null;
};

export function ChatWindow({ messages, loading = false, errorText }: Props) {
  return (
    <div style={{ padding: '12px', overflowY: 'auto', flex: 1, background: '#fafafa' }}>
      {messages.map((m, i) => (
        <div key={i} style={{ marginBottom: '8px' }}>
          <strong>{m.role === 'user' ? 'You' : m.role === 'assistant' ? 'AMC' : 'System'}:</strong> {m.content}
          {m.traceId ? <span style={{ color: '#777', marginLeft: '6px' }}>(trace: {m.traceId})</span> : null}
        </div>
      ))}

      {loading ? <div style={{ color: '#555' }}>AMC is thinking...</div> : null}
      {errorText ? <div style={{ color: '#b00020', marginTop: '8px' }}>{errorText}</div> : null}
    </div>
  );
}
