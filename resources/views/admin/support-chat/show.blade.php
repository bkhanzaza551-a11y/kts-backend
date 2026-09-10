@extends('layouts.app')
@section('title', 'Support Chat - ' . $ticket->ticket_number)
@section('content')

{{-- Breadcrumb and Action Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('admin.support-chat.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $ticket->ticket_number }}</span>
            @if($ticket->source == 'ai_chatbot')
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 0.72rem;">
                    <i class="bi bi-robot me-1"></i>AI Bot Escalation
                </span>
            @endif
            @if($ticket->status == 'open')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">🟢 Open</span>
            @elseif($ticket->status == 'in_progress')
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">🟡 In Progress</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">⚪ Closed</span>
            @endif
        </div>
        <h4 class="mb-0 fw-bold text-dark">{{ $ticket->subject }}</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($ticket->status == 'open')
            <form method="POST" action="{{ route('admin.support-chat.close', $ticket) }}" onsubmit="return confirm('Are you sure you want to mark this ticket as closed?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold px-3">
                    <i class="bi bi-check2-circle me-1"></i>Close Ticket
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.support-chat.reopen', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm fw-semibold px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reopen Ticket
                </button>
            </form>
        @endif
        <a href="{{ route('admin.support-chat.index') }}" class="btn btn-light btn-sm border fw-semibold px-3">
            <i class="bi bi-list-ul me-1"></i>All Tickets
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
    <div class="d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    {{-- Left: Conversation Thread & Reply Box --}}
    <div class="col-lg-8">
        {{-- Chat Messenger Window --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Conversation Thread</h6>
                        <small class="text-secondary">{{ $replies->count() + 1 }} message(s) in this thread</small>
                    </div>
                </div>
                <div class="text-secondary small">
                    <i class="bi bi-clock me-1"></i>Started {{ $ticket->created_at->diffForHumans() }}
                </div>
            </div>

            <div class="card-body p-4" style="background-color: #f8fafc; min-height: 480px; max-height: 560px; overflow-y: auto;" id="chatMessages">
                
                {{-- Ticket Original Question / Problem Description --}}
                <div class="d-flex mb-4 justify-content-start">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-2 flex-shrink-0 shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                        {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div style="max-width: 80%;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold text-dark small">{{ $ticket->user->name ?? 'Customer' }}</span>
                            <span class="badge bg-primary-subtle text-primary px-2" style="font-size: 0.65rem;">Client</span>
                            <span class="text-muted" style="font-size: 0.72rem;">{{ $ticket->created_at->format('M d, Y • h:i A') }}</span>
                        </div>
                        <div class="p-3 bg-white border shadow-sm rounded-4 rounded-top-start-0 text-dark">
                            <div class="fw-semibold text-primary mb-1 small"><i class="bi bi-info-circle me-1"></i>Initial Ticket Request:</div>
                            <div style="white-space: pre-wrap; word-break: break-word; line-height: 1.55;">{{ $ticket->description ?: $ticket->subject }}</div>
                            
                            @if($ticket->attachment)
                                <div class="mt-3 pt-2 border-top">
                                    @php
                                        $ext = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
                                    @endphp
                                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <div class="position-relative d-inline-block">
                                            <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="d-block text-decoration-none">
                                                <img src="{{ asset('storage/' . $ticket->attachment) }}" class="rounded-3 border shadow-sm" style="max-width: 240px; max-height: 180px; object-fit: cover;" alt="Attachment">
                                                <div class="small text-primary mt-1"><i class="bi bi-arrows-fullscreen me-1"></i>Click to view full image</div>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-arrow-down-fill text-primary fs-5"></i>
                                            <div class="text-start">
                                                <div class="fw-semibold text-dark small">Download Attached File</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ strtoupper($ext) }} File</div>
                                            </div>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Replies Stream --}}
                @foreach($replies as $reply)
                    @php
                        $isUser = $reply->user_id == $ticket->user_id && !$reply->is_system;
                        $isAdmin = !$isUser && !$reply->is_system;
                        $isSystem = (bool)$reply->is_system;
                    @endphp

                    @if($isSystem)
                        {{-- System / Bot Message --}}
                        <div class="d-flex justify-content-center my-3">
                            <div class="bg-info-subtle border border-info-subtle text-info-emphasis px-3 py-2 rounded-pill small shadow-sm d-flex align-items-center gap-2" style="max-width: 85%;">
                                <i class="bi bi-robot text-info"></i>
                                <span><strong>System / AI:</strong> {{ $reply->message }}</span>
                                <span class="text-muted opacity-75 ms-1" style="font-size: 0.68rem;">{{ $reply->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    @elseif($isUser)
                        {{-- User Reply (Left) --}}
                        <div class="d-flex mb-3 justify-content-start">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-2 flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div style="max-width: 78%;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-dark small">{{ $reply->user->name ?? 'User' }}</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $reply->created_at->format('M d • h:i A') }}</span>
                                </div>
                                <div class="p-3 bg-white border shadow-sm rounded-4 rounded-top-start-0 text-dark">
                                    <div style="white-space: pre-wrap; word-break: break-word; line-height: 1.55;">{{ $reply->message }}</div>
                                    @if($reply->attachment)
                                        <div class="mt-2 pt-2 border-top">
                                            @php $ext = strtolower(pathinfo($reply->attachment, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="d-block text-decoration-none">
                                                    <img src="{{ asset('storage/' . $reply->attachment) }}" class="rounded-3 border shadow-sm" style="max-width: 220px; max-height: 160px; object-fit: cover;" alt="Attachment">
                                                    <div class="small text-primary mt-1"><i class="bi bi-box-arrow-up-right me-1"></i>View image</div>
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2">
                                                    <i class="bi bi-paperclip text-primary fs-5"></i>
                                                    <span class="small fw-semibold">View {{ strtoupper($ext) }} Attachment</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Admin Reply (Right) --}}
                        <div class="d-flex mb-3 justify-content-end">
                            <div style="max-width: 78%;" class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2 mb-1">
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $reply->created_at->format('M d • h:i A') }}</span>
                                    <span class="badge bg-success-subtle text-success px-2" style="font-size: 0.65rem;">Support Agent</span>
                                    <span class="fw-bold text-dark small">{{ $reply->user->name ?? 'Admin' }}</span>
                                </div>
                                <div class="p-3 bg-primary text-white shadow-sm rounded-4 rounded-top-end-0 text-start">
                                    <div style="white-space: pre-wrap; word-break: break-word; line-height: 1.55;">{{ $reply->message }}</div>
                                    @if($reply->attachment)
                                        <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                                            @php $ext = strtolower(pathinfo($reply->attachment, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="d-block text-decoration-none">
                                                    <img src="{{ asset('storage/' . $reply->attachment) }}" class="rounded-3 border border-white border-opacity-50 shadow-sm" style="max-width: 220px; max-height: 160px; object-fit: cover;" alt="Attachment">
                                                    <div class="small text-white-50 mt-1"><i class="bi bi-box-arrow-up-right me-1"></i>View attachment</div>
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2">
                                                    <i class="bi bi-paperclip text-primary fs-5"></i>
                                                    <span class="small fw-semibold">View {{ strtoupper($ext) }} File</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold ms-2 flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Reply Composer Box --}}
        @if($ticket->status == 'open')
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                {{-- Quick Canned Responses Pill Bar --}}
                <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-secondary fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Quick Replies:</span>
                    <button type="button" class="btn btn-light btn-sm border py-1 px-2 text-dark canned-btn" data-text="Hello! Thank you for reaching out. We are currently looking into this for you and will update you shortly.">
                        🔍 Investigating
                    </button>
                    <button type="button" class="btn btn-light btn-sm border py-1 px-2 text-dark canned-btn" data-text="Your request has been processed and successfully resolved. Please verify on your mobile app.">
                        ✅ Issue Resolved
                    </button>
                    <button type="button" class="btn btn-light btn-sm border py-1 px-2 text-dark canned-btn" data-text="Could you please provide a screenshot or screen recording of the error so we can assist you better?">
                        📸 Request Screenshot
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.support-chat.reply', $ticket) }}" enctype="multipart/form-data" id="replyForm">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" id="replyMessage" class="form-control" rows="3" placeholder="Type your reply here... (Press Ctrl + Enter to send)" required style="border-radius: 12px; font-size: 0.95rem; line-height: 1.5;"></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="btn btn-light btn-sm border fw-semibold mb-0" for="chatAttachmentInput" style="cursor: pointer;">
                                <i class="bi bi-paperclip me-1 text-primary"></i>Attach File
                            </label>
                            <input type="file" name="attachment" id="chatAttachmentInput" class="d-none" accept="image/*,.pdf,.mp4" onchange="handleFileSelected(this)">
                            <div id="filePreviewBadge" class="d-none badge bg-light text-dark border p-2 align-items-center gap-2">
                                <i class="bi bi-file-earmark-check-fill text-success"></i>
                                <span id="fileNameDisplay" class="fw-normal font-monospace"></span>
                                <button type="button" class="btn-close" style="font-size: 0.6rem;" onclick="clearAttachment()"></button>
                            </div>
                            <small class="text-secondary d-none d-md-inline" style="font-size: 0.75rem;">Max 10MB (Images, PDF, MP4)</small>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold" style="border-radius: 10px;">
                            <i class="bi bi-send-fill me-1"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm bg-light" style="border-radius: 16px;">
            <div class="card-body p-4 text-center">
                <div class="text-secondary mb-2">
                    <i class="bi bi-lock-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">This support ticket is closed</h6>
                <p class="text-secondary small mb-3">Reopen this ticket if you wish to send additional replies to the customer.</p>
                <form method="POST" action="{{ route('admin.support-chat.reopen', $ticket) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm px-3 fw-semibold">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reopen Ticket to Reply
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: User Profile & Ticket Details Sidebar --}}
    <div class="col-lg-4">
        {{-- User Profile Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-circle text-primary me-2"></i>Customer Profile</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 52px; height: 52px; font-size: 1.25rem;">
                        {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $ticket->user->name ?? 'Unknown User' }}</h6>
                        <span class="text-secondary small">{{ $ticket->user->email ?? 'No email provided' }}</span>
                        <div class="mt-1">
                            @if(isset($ticket->user) && $ticket->user->is_banned)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2" style="font-size: 0.65rem;">Banned</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2" style="font-size: 0.65rem;">Active Member</span>
                            @endif
                            @if(isset($ticket->user) && $ticket->user->is_premium)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2" style="font-size: 0.65rem;"><i class="bi bi-star-fill me-1"></i>VIP / Premium</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3 small">
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-telephone me-2"></i>Phone:</span>
                        <span class="fw-semibold text-dark">{{ $ticket->user->phone ?? 'Not set' }}</span>
                    </div>
                    @if(isset($ticket->user->whatsapp) && $ticket->user->whatsapp)
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp:</span>
                        <span class="fw-semibold text-dark">{{ $ticket->user->whatsapp }}</span>
                    </div>
                    @endif
                    @if(isset($ticket->user->country) && $ticket->user->country)
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-geo-alt me-2"></i>Location:</span>
                        <span class="fw-semibold text-dark">{{ $ticket->user->city ? $ticket->user->city . ', ' : '' }}{{ $ticket->user->country }}</span>
                    </div>
                    @endif
                    @if(isset($ticket->user->real_account_id) && $ticket->user->real_account_id)
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Live Trading ID:</span>
                        <span class="font-monospace fw-bold text-success">{{ $ticket->user->real_account_id }}</span>
                    </div>
                    @endif
                    @if(isset($ticket->user->demo_account_id) && $ticket->user->demo_account_id)
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-laptop me-2 text-info"></i>Demo Account ID:</span>
                        <span class="font-monospace fw-bold text-info">{{ $ticket->user->demo_account_id }}</span>
                    </div>
                    @endif
                    @if(isset($ticket->user->created_at))
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-calendar-check me-2"></i>Joined:</span>
                        <span class="text-dark">{{ $ticket->user->created_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>

                @if(isset($ticket->user))
                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('admin.users.index', ['search' => $ticket->user->email]) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-person-lines-fill me-1"></i>View User in Admin
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Ticket Metadata Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Ticket Info</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3 small">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Ticket Number:</span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="font-monospace fw-bold text-dark" id="ticketNumberText">{{ $ticket->ticket_number }}</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-secondary" onclick="copyTicketNumber()" title="Copy Ticket Number">
                                <i class="bi bi-clipboard" id="copyIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Status:</span>
                        @if($ticket->status == 'open')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">🟢 Open</span>
                        @elseif($ticket->status == 'in_progress')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">🟡 In Progress</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">⚪ Closed</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Priority:</span>
                        @if($ticket->priority == 'high')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">High</span>
                        @elseif($ticket->priority == 'medium')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Medium</span>
                        @else
                            <span class="badge bg-light text-secondary border px-2 py-1">Low</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Origin / Source:</span>
                        @if($ticket->source == 'ai_chatbot')
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="bi bi-robot me-1"></i>AI Escalated</span>
                        @else
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-phone me-1"></i>Mobile App</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Opened At:</span>
                        <span class="text-dark">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Last Activity:</span>
                        <span class="text-dark">{{ $replies->last() ? $replies->last()->created_at->diffForHumans() : $ticket->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll chat to the very bottom
    var chatBox = document.getElementById('chatMessages');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Canned Response insert helper
    document.querySelectorAll('.canned-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var text = this.getAttribute('data-text');
            var textarea = document.getElementById('replyMessage');
            if (textarea) {
                if (textarea.value.trim().length > 0) {
                    textarea.value += "\n" + text;
                } else {
                    textarea.value = text;
                }
                textarea.focus();
            }
        });
    });

    // Keyboard shortcut: Ctrl + Enter / Cmd + Enter to submit
    var replyTextarea = document.getElementById('replyMessage');
    if (replyTextarea) {
        replyTextarea.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('replyForm').submit();
            }
        });
    }
});

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        document.getElementById('fileNameDisplay').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        var badge = document.getElementById('filePreviewBadge');
        badge.classList.remove('d-none');
        badge.classList.add('d-inline-flex');
    }
}

function clearAttachment() {
    var input = document.getElementById('chatAttachmentInput');
    if (input) input.value = '';
    var badge = document.getElementById('filePreviewBadge');
    badge.classList.add('d-none');
    badge.classList.remove('d-inline-flex');
}

function copyTicketNumber() {
    var text = document.getElementById('ticketNumberText').textContent.trim();
    navigator.clipboard.writeText(text).then(function() {
        var icon = document.getElementById('copyIcon');
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(function() {
            icon.className = 'bi bi-clipboard';
        }, 2000);
    });
}
</script>
@endsection

