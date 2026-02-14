export interface ChatMessage {
  role: 'user' | 'assistant' | 'system';
  content: string;
  traceId?: string;
  intent?: string;
  meta?: Record<string, unknown>;
  response?: {
    summary?: string;
    action?: string;
    details?: string[];
    highlights?: string[];
    data?: Record<string, unknown>[];
    intelligence?: {
      severity?: string;
      signal?: string;
      action?: string;
      insight?: string | null;
      metrics?: Record<string, unknown>;
    };
    needs_clarification?: boolean;
    follow_up_question?: string | null;
    [key: string]: unknown;
  };
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
      action?: string;
      details?: string[];
      highlights?: string[];
      data?: Record<string, unknown>[];
      intelligence?: Record<string, unknown>;
      needs_clarification?: boolean;
      follow_up_question?: string | null;
      [key: string]: unknown;
    };
    meta?: Record<string, unknown>;
  } | null;
  error: ApiError | null;
}
