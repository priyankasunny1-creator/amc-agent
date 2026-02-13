import { useMemo, useState } from 'react';
import { ChatInput } from './ChatInput';
import { ChatWindow } from './ChatWindow';
import { ApiResponseEnvelope, ChatMessage } from '../types/ChatTypes';

function createConversationId(): string {
  return `conv_${Math.random().toString(36).slice(2, 10)}`;
}

export function ChatApp() {
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorText, setErrorText] = useState<string | null>(null);
  const [conversationId, setConversationId] = useState<string>(() => createConversationId());

  const conversationMeta = useMemo(
    () => ({ conversationId }),
    [conversationId]
  );

  const sendMessage = async (text: string) => {
    const userMsg: ChatMessage = { role: 'user', content: text };
    setMessages(m => [...m, userMsg]);
    setLoading(true);
    setErrorText(null);

    try {
      const res = await fetch('/api/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: text,
          conversation_id: conversationMeta.conversationId
        })
      });

      const payload = (await res.json()) as ApiResponseEnvelope;

      if (payload.conversation_id && payload.conversation_id !== conversationMeta.conversationId) {
        // Keep local client in sync if server rotates/assigns a new conversation id.
        setConversationId(payload.conversation_id);
      }

      if (!payload.ok) {
        const msg = payload.error?.message || 'Unknown backend error.';
        setErrorText(`${msg} (trace: ${payload.trace_id})`);
        setMessages(m => [
          ...m,
          { role: 'system', content: `Request failed: ${msg}`, traceId: payload.trace_id }
        ]);
        return;
      }

      const summary = payload.data?.response?.summary || 'No response summary was provided.';
      setMessages(m => [
        ...m,
        {
          role: 'assistant',
          content: summary,
          traceId: payload.trace_id
        }
      ]);
    } catch (error) {
      const msg = error instanceof Error ? error.message : 'Network error';
      setErrorText(msg);
      setMessages(m => [...m, { role: 'system', content: `Network error: ${msg}` }]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100vh' }}>
      <div style={{ padding: '8px 12px', borderBottom: '1px solid #ddd', fontSize: '12px', color: '#666' }}>
        Conversation: {conversationMeta.conversationId}
      </div>
      <ChatWindow messages={messages} loading={loading} errorText={errorText} />
      <ChatInput onSend={sendMessage} disabled={loading} />
    </div>
  );
}
