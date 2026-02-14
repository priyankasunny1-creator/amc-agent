import { ChatMessage } from '../types/ChatTypes';

type Props = {
  messages: ChatMessage[];
  loading?: boolean;
  errorText?: string | null;
};

function renderTable(rows: Record<string, unknown>[]) {
  if (!rows.length) return null;
  const keys = Object.keys(rows[0]);

  return (
    <div style={{ overflowX: 'auto', marginTop: '8px' }}>
      <table style={{ borderCollapse: 'collapse', width: '100%', fontSize: '12px' }}>
        <thead>
          <tr>
            {keys.map(k => (
              <th key={k} style={{ border: '1px solid #ddd', padding: '6px', textAlign: 'left', background: '#f2f2f2' }}>{k}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.slice(0, 10).map((row, i) => (
            <tr key={i}>
              {keys.map(k => (
                <td key={k} style={{ border: '1px solid #eee', padding: '6px' }}>{String(row[k] ?? '')}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export function ChatWindow({ messages, loading = false, errorText }: Props) {
  return (
    <div style={{ padding: '12px', overflowY: 'auto', flex: 1, background: '#fafafa' }}>
      {messages.map((m, i) => (
        <div key={i} style={{ marginBottom: '12px', padding: '10px', background: '#fff', border: '1px solid #eee', borderRadius: '8px' }}>
          <strong>{m.role === 'user' ? 'You' : m.role === 'assistant' ? 'AMC' : 'System'}:</strong> {m.content}
          {m.traceId ? <span style={{ color: '#777', marginLeft: '6px' }}>(trace: {m.traceId})</span> : null}

          {m.role === 'assistant' && m.intent ? (
            <div style={{ marginTop: '6px', color: '#555', fontSize: '12px' }}>
              Intent: <code>{m.intent}</code>
              {' | '}Confidence: <strong>{String(m.meta?.confidence ?? 'n/a')}</strong>
              {' | '}Rows: <strong>{String(m.meta?.row_count ?? 'n/a')}</strong>
            </div>
          ) : null}

          {Array.isArray(m.response?.details) && m.response?.details.length ? (
            <ul style={{ marginTop: '8px', paddingLeft: '20px' }}>
              {m.response.details.map((d, idx) => <li key={idx}>{d}</li>)}
            </ul>
          ) : null}

          {Array.isArray(m.response?.highlights) && m.response?.highlights.length ? (
            <div style={{ marginTop: '8px' }}>
              <strong>Highlights:</strong>
              <ul style={{ marginTop: '4px', paddingLeft: '20px' }}>
                {m.response.highlights.map((h, idx) => <li key={idx}>{h}</li>)}
              </ul>
            </div>
          ) : null}

          {Array.isArray(m.response?.data) ? renderTable(m.response.data) : null}


          {m.response?.intelligence ? (
            <div style={{ marginTop: '8px', fontSize: '12px', color: '#444', background: '#f8f9ff', border: '1px solid #e6e9ff', padding: '8px', borderRadius: '6px' }}>
              <strong>Intelligence:</strong>{' '}
              Severity <b>{String(m.response.intelligence.severity ?? 'n/a')}</b>
              {m.response.intelligence.signal ? <> | Signal: {String(m.response.intelligence.signal)}</> : null}
              {m.response.intelligence.insight ? <> | Insight: {String(m.response.intelligence.insight)}</> : null}
            </div>
          ) : null}

          {m.response?.action ? <div style={{ marginTop: '8px', color: '#0b5394' }}>Next action: {m.response.action}</div> : null}
          {m.response?.follow_up_question ? <div style={{ marginTop: '8px', color: '#7a4f01' }}>Need from you: {m.response.follow_up_question}</div> : null}
        </div>
      ))}

      {loading ? <div style={{ color: '#555' }}>AMC is analyzing your request...</div> : null}
      {errorText ? <div style={{ color: '#b00020', marginTop: '8px' }}>{errorText}</div> : null}
    </div>
  );
}
