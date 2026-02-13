export interface ChatMessage {
  role: 'user' | 'assistant' | 'system';
  content: string;
  traceId?: string;
}

export interface ApiError {
  code: string;
  message: string;
  details?: Record<string, unknown>;
}

export interface ApiResponseEnvelope {
  ok: boolean;
  status: number;
  trace_id: string;
  conversation_id: string;
  data: {
    intent?: string;
    response?: {
      summary?: string;
      needs_clarification?: boolean;
      confidence?: string;
      [key: string]: unknown;
    };
    meta?: Record<string, unknown>;
  } | null;
  error: ApiError | null;
}
