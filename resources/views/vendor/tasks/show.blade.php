@extends('vendor.layouts.app')

@section('title', 'Task Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Task Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">{{ $task->title }}</h4>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'question' => 'danger',
                                            'done' => 'primary',
                                            'verified' => 'success'
                                        ];
                                        $statusColor = $statusColors[$task->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }} rounded-pill mt-1">
                                        {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('vendor.tasks.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Task Description -->
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-align-left text-primary me-2"></i>Description
                                            </h5>
                                            <p class="text-muted">{{ $task->description }}</p>
                                        </div>

                                        <!-- Attachment -->
                                        @if($task->attachment)
                                            <div class="mb-4">
                                                <h5 class="fw-bold mb-3">
                                                    <i class="fas fa-paperclip text-primary me-2"></i>Attachment
                                                </h5>
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-file text-primary me-2"></i>
                                                            <span>{{ basename($task->attachment) }}</span>
                                                        </div>
                                                        <a href="{{ asset('storage/' . $task->attachment) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-primary rounded-pill">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Modern Chat Interface -->
                                        <div class="mb-4">
                                            <div class="chat-header d-flex justify-content-between align-items-center mb-3 p-3 bg-gradient rounded-3 shadow-sm" style="background: linear-gradient(135deg, {{ $task->status === 'verified' ? '#6c757d 0%, #495057 100%' : '#667eea 0%, #764ba2 100%' }});">
                                                <div class="d-flex align-items-center">
                                                    <div class="chat-icon me-3">
                                                        <i class="fas fa-{{ $task->status === 'verified' ? 'lock' : 'comments' }} fa-2x text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0 text-white fw-bold">{{ $task->status === 'verified' ? 'Task Locked' : 'Conversation' }}</h5>
                                                        <small class="text-white opacity-75">
                                                            @if($task->status === 'verified')
                                                                This task is verified and locked
                                                            @else
                                                                {{ $task->comments->where('is_internal', 0)->count() }} messages
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="chat-status">
                                                    <span class="badge bg-white px-3 py-2 {{ $task->status === 'verified' ? 'text-secondary' : 'text-primary' }}">
                                                        @if($task->status === 'verified')
                                                            <i class="fas fa-lock me-1" style="font-size: 10px;"></i>Locked
                                                        @else
                                                            <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>Active
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            @php
                                                $visibleComments = $task->comments->where('is_internal', 0)->sortBy('created_at');
                                                $currentUserId = Auth::id();
                                            @endphp

                                            <!-- Chat Container -->
                                            <div class="modern-chat-container shadow-lg rounded-4 overflow-hidden">
                                                <!-- Messages Area -->
                                                <div class="chat-messages-area p-4" id="chatMessages">
                                                    @if($visibleComments->count() > 0)
                                                        @foreach($visibleComments as $comment)
                                                            @php
                                                                $isCurrentUser = $comment->user_id == $currentUserId;
                                                                $userName = $comment->user->name ?? 'Unknown User';
                                                                $initials = strtoupper(substr($userName, 0, 1));
                                                            @endphp
                                                            
                                                            <div class="message-row mb-4 {{ $isCurrentUser ? 'message-sent' : 'message-received' }}">
                                                                <div class="d-flex {{ $isCurrentUser ? 'flex-row-reverse' : 'flex-row' }} align-items-end">
                                                                    <!-- Avatar -->
                                                                    <div class="message-avatar {{ $isCurrentUser ? 'ms-2' : 'me-2' }}">
                                                                        <div class="avatar-circle {{ $isCurrentUser ? 'bg-primary' : 'bg-secondary' }}">
                                                                            {{ $initials }}
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Message Content -->
                                                                    <div class="message-content" style="max-width: 65%;">
                                                                        <!-- Name & Time -->
                                                                        <div class="message-meta mb-1 {{ $isCurrentUser ? 'text-end' : 'text-start' }}">
                                                                            <span class="message-author">{{ $userName }}</span>
                                                                            <span class="message-time">{{ $comment->created_at->format('g:i A') }}</span>
                                                                        </div>
                                                                        
                                                                        <!-- Message Bubble -->
                                                                        <div class="message-bubble {{ $isCurrentUser ? 'bubble-sent' : 'bubble-received' }}">
                                                                            <p class="message-text mb-0">{{ $comment->comment }}</p>
                                                                            <div class="message-footer mt-1">
                                                                                <small class="message-date">{{ $comment->created_at->format('M d') }}</small>
                                                                                @if($isCurrentUser)
                                                                                    <i class="fas fa-check-double ms-2 text-white opacity-75"></i>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="empty-chat text-center py-5">
                                                            <div class="empty-icon mb-3">
                                                                <i class="fas fa-comment-dots fa-4x text-muted opacity-25"></i>
                                                            </div>
                                                            <h6 class="text-muted mb-2">No messages yet</h6>
                                                            <p class="text-muted small">Start the conversation by sending a message below</p>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Chat Input Area -->
                                                @if($task->status !== 'verified')
                                                    <div class="chat-input-area">
                                                        <form action="{{ route('vendor.tasks.comment', $task->id) }}" method="POST" id="chatForm" class="h-100">
                                                            @csrf
                                                            <div class="input-wrapper">
                                                                <div class="input-actions">
                                                                    <button type="button" class="action-btn" title="Emoji">
                                                                        <i class="far fa-smile"></i>
                                                                    </button>
                                                                </div>
                                                                
                                                                <textarea class="chat-input @error('comment') is-invalid @enderror" 
                                                                          name="comment" 
                                                                          id="commentInput"
                                                                          rows="1" 
                                                                          placeholder="Type a message..." 
                                                                          required></textarea>
                                                                
                                                                <button type="submit" class="send-btn">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            </div>
                                                            @error('comment')
                                                                <div class="text-danger small px-3 pb-2">{{ $message }}</div>
                                                            @enderror
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="chat-input-area-locked">
                                                        <div class="locked-message text-center py-3">
                                                            <i class="fas fa-lock me-2 text-muted"></i>
                                                            <span class="text-muted">This task is verified and locked. No further changes can be made.</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <!-- Task Information -->
                                        <div class="card border-0 bg-light mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title fw-bold mb-3">
                                                    <i class="fas fa-info-circle text-primary me-2"></i>Task Information
                                                </h5>

                                                <div class="mb-3">
                                                    <strong>Assigned By:</strong><br>
                                                    <span class="text-muted">
                                                        {{ $task->assignedBy->name ?? 'N/A' }}
                                                        @if($task->assignedBy)
                                                            <br><small>{{ $task->assignedBy->email }}</small>
                                                        @endif
                                                    </span>
                                                </div>

                                                <div class="mb-3">
                                                    <strong>Created:</strong><br>
                                                    <span class="text-muted">{{ $task->created_at->format('M d, Y h:i A') }}</span>
                                                </div>

                                                <div class="mb-3">
                                                    <strong>Last Updated:</strong><br>
                                                    <span class="text-muted">{{ $task->updated_at->format('M d, Y h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h5 class="card-title fw-bold mb-3">
                                                    <i class="fas fa-bolt text-primary me-2"></i>Update Status
                                                </h5>
                                                
                                                @if($task->status === 'verified')
                                                    <div class="alert alert-success mb-0">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-lock fa-2x me-3"></i>
                                                            <div>
                                                                <strong>Task Verified & Locked</strong>
                                                                <p class="mb-0 small mt-1">This task has been verified and cannot be modified.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-grid gap-2">
                                                        @if($task->status === 'pending')
                                                            <form action="{{ route('vendor.tasks.status', $task->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_progress">
                                                                <button type="submit" class="btn btn-info rounded-pill w-100">
                                                                    <i class="fas fa-play me-2"></i>Start Working
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if(in_array($task->status, ['pending', 'in_progress', 'question']))
                                                            <form action="{{ route('vendor.tasks.status', $task->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="question">
                                                                <button type="submit" class="btn btn-warning rounded-pill w-100">
                                                                    <i class="fas fa-question-circle me-2"></i>Raise Question
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if(in_array($task->status, ['pending', 'in_progress', 'question']))
                                                            <form action="{{ route('vendor.tasks.status', $task->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="done">
                                                                <button type="submit" class="btn btn-primary rounded-pill w-100">
                                                                    <i class="fas fa-check me-2"></i>Mark as Done
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($task->status === 'done' && Auth::user()->isVendor())
                                                            <form action="{{ route('vendor.tasks.verify', $task->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success rounded-pill w-100">
                                                                    <i class="fas fa-check-double me-2"></i>Verify Task
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.gap-2 {
    gap: 0.5rem !important;
}
.d-grid.gap-2 {
    gap: 0.5rem !important;
}

/* Modern Chat Styles */
.modern-chat-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-header {
    border: none !important;
}

.chat-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
}

.chat-messages-area {
    flex: 1;
    overflow-y: auto;
    background: linear-gradient(to bottom, #f9fafb 0%, #f3f4f6 100%);
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(120, 119, 198, 0.03) 0%, transparent 50%),
        linear-gradient(to bottom, #f9fafb, #f3f4f6);
}

.chat-messages-area::-webkit-scrollbar {
    width: 8px;
}

.chat-messages-area::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages-area::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.chat-messages-area::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Message Rows */
.message-row {
    animation: messageSlideIn 0.3s ease-out;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Avatar */
.message-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Message Content */
.message-meta {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.message-author {
    font-weight: 600;
    color: #374151;
}

.message-time {
    margin-left: 8px;
    color: #9ca3af;
}

/* Message Bubbles */
.message-bubble {
    position: relative;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.message-bubble:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.bubble-sent {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.bubble-received {
    background: white;
    color: #1f2937;
    border-bottom-left-radius: 4px;
    border: 1px solid #e5e7eb;
}

.message-text {
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
    white-space: pre-wrap;
}

.message-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    font-size: 11px;
    opacity: 0.8;
}

.bubble-sent .message-footer {
    color: white;
}

.bubble-received .message-footer {
    color: #6b7280;
}

/* Empty State */
.empty-chat {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.empty-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Chat Input Area */
.chat-input-area {
    border-top: 1px solid #e5e7eb;
    background: white;
    padding: 16px;
}

.input-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f3f4f6;
    border-radius: 24px;
    padding: 8px 16px;
    transition: all 0.3s ease;
}

.input-wrapper:focus-within {
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 20px;
    cursor: pointer;
    padding: 4px;
    transition: all 0.2s ease;
}

.action-btn:hover {
    color: #667eea;
    transform: scale(1.1);
}

.chat-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    resize: none;
    font-size: 14px;
    color: #1f2937;
    padding: 4px 0;
    max-height: 120px;
    overflow-y: auto;
}

.chat-input::placeholder {
    color: #9ca3af;
}

.chat-input::-webkit-scrollbar {
    width: 4px;
}

.chat-input::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.send-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-btn:active {
    transform: scale(0.95);
}

/* Responsive */
@media (max-width: 768px) {
    .modern-chat-container {
        height: 500px;
    }
    
    .message-bubble {
        max-width: 85% !important;
    }
    
    .avatar-circle {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
}

/* Locked State Styles */
.chat-input-area-locked {
    background: #f8f9fa;
    border-top: 2px solid #dee2e6;
    padding: 16px;
}

.locked-message {
    background: white;
    border: 2px dashed #ced4da;
    border-radius: 12px;
    padding: 16px;
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.locked-message i {
    font-size: 18px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const commentInput = document.getElementById('commentInput');
    const chatForm = document.getElementById('chatForm');
    
    // Auto-scroll to bottom
    if (chatMessages) {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }

    // Handle Enter key (Shift+Enter for new line)
    if (commentInput && chatForm) {
        commentInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim()) {
                    chatForm.submit();
                }
            }
        });

        // Auto-resize textarea
        commentInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Focus on input
        commentInput.focus();
    }

    // Smooth scroll animation
    if (chatMessages) {
        chatMessages.style.scrollBehavior = 'smooth';
    }
});
</script>
@endsection
