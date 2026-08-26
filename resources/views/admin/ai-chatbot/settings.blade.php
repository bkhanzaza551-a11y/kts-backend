@extends('layouts.app')
@section('title', 'AI Chatbot Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-robot me-2 text-primary"></i>AI Chatbot Settings</h4>
    <a href="{{ route('admin.ai-chatbot.chat-logs') }}" class="btn btn-outline-info btn-sm"><i class="bi bi-chat-left-text me-1"></i>Chat Logs</a>
</div>
<form method="POST" action="{{ route('admin.ai-chatbot.update-settings') }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">System Prompt</h6></div>
                <div class="card-body">
                    <label class="form-label text-secondary">System Prompt <span class="text-danger">*</span></label>
                    <textarea name="system_prompt" class="form-control @error('system_prompt') is-invalid @enderror" rows="10" maxlength="10000" required>{{ old('system_prompt', $settings['system_prompt']->value ?? '') }}</textarea>
                    @error('system_prompt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-secondary">This defines the AI's personality and trading knowledge.</small>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h6 class="mb-0">API Configuration</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Groq API Key</label>
                            <input type="password" name="groq_api_key" class="form-control @error('groq_api_key') is-invalid @enderror" value="{{ old('groq_api_key') }}" maxlength="500" placeholder="gsk_...">
                            @error('groq_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">OpenAI API Key</label>
                            <input type="password" name="openai_api_key" class="form-control @error('openai_api_key') is-invalid @enderror" value="{{ old('openai_api_key') }}" maxlength="500" placeholder="sk-...">
                            @error('openai_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Model Settings</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Model <span class="text-danger">*</span></label>
                        <select name="model" class="form-select" required>
                            <option value="llama3-70b-8192" {{ ($settings['model']->value ?? '') === 'llama3-70b-8192' ? 'selected' : '' }}>Llama 3 70B (Groq)</option>
                            <option value="llama3-8b-8192" {{ ($settings['model']->value ?? '') === 'llama3-8b-8192' ? 'selected' : '' }}>Llama 3 8B (Groq)</option>
                            <option value="mixtral-8x7b-32768" {{ ($settings['model']->value ?? '') === 'mixtral-8x7b-32768' ? 'selected' : '' }}>Mixtral 8x7B (Groq)</option>
                            <option value="gpt-4o" {{ ($settings['model']->value ?? '') === 'gpt-4o' ? 'selected' : '' }}>GPT-4o (OpenAI)</option>
                            <option value="gpt-4o-mini" {{ ($settings['model']->value ?? '') === 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (OpenAI)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Max Tokens</label>
                        <input type="number" name="max_tokens" class="form-control" value="{{ old('max_tokens', $settings['max_tokens']->value ?? 2048) }}" min="100" max="8000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Temperature (0-2)</label>
                        <input type="number" name="temperature" class="form-control" value="{{ old('temperature', $settings['temperature']->value ?? 0.7) }}" min="0" max="2" step="0.1">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="isEnabled" {{ ($settings['is_enabled']->value ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label text-secondary" for="isEnabled">Enable AI Chatbot</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="tools_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="tools_enabled" value="1" id="toolsEnabled" {{ ($settings['tools_enabled']->value ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label text-secondary" for="toolsEnabled">Enable Agent Tools</label>
                        <small class="d-block text-muted">Allow AI to check subscriptions, bots, emails, send notifications</small>
                    </div>
                </div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Save Settings</button></div>
        </div>
    </div>
</form>
@endsection
