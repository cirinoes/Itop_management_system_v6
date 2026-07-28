<?php use App\Core\Security; use App\Core\View; use App\Core\Auth; ?>
<section class="container-fluid py-4 px-lg-5">
    <?php View::partial('partials/role-nav'); ?>
    
    <style>
        .mail-layout {
            height: calc(100vh - 200px);
            min-height: 600px;
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        .mail-sidebar {
            border-right: 1px solid #eaeaea;
            background: #fdfdfd;
            display: flex;
            flex-direction: column;
        }
        .mail-list {
            overflow-y: auto;
            flex-grow: 1;
        }
        .mail-list-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .mail-list-item:hover {
            background-color: #f8f9fa;
        }
        .mail-list-item.active {
            background-color: #f0f7ff;
            border-left: 3px solid var(--bs-primary);
        }
        .mail-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bs-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .mail-content-area {
            display: flex;
            flex-direction: column;
            background: #f9fafb;
        }
        .mail-thread {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }
        .mail-message-card {
            background: #fff;
            border-radius: 0.5rem;
            border: 1px solid #eaeaea;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .mail-reply-box {
            background: #fff;
            border-top: 1px solid #eaeaea;
            padding: 1.5rem;
        }
        .reply-card {
            border: 1px solid #eaeaea;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .reply-card textarea {
            border: none;
            box-shadow: none;
            resize: none;
            min-height: 120px;
        }
        .reply-card textarea:focus {
            box-shadow: none;
        }
        .reply-toolbar {
            padding: 0.75rem 1rem;
            background: #fff;
            border-top: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .custom-file-upload {
            cursor: pointer;
            padding: 0.5rem;
            color: #6c757d;
            border-radius: 0.25rem;
            transition: 0.2s;
        }
        .custom-file-upload:hover {
            background: #f8f9fa;
            color: var(--bs-primary);
        }

        /* Center modal relative to the main content area, offsetting the sidebar */
        @media (min-width: 992px) {
            #composeModal {
                padding-left: var(--sidenav-width) !important;
            }
        }
    </style>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <span class="section-label">Communication</span>
            <h1 class="section-title mb-0">Messages</h1>
        </div>
        <button class="btn btn-primary shadow-sm px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#composeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Compose
        </button>
    </div>

    <!-- Main Mail Interface -->
    <div class="row g-0 mail-layout">
        <!-- Sidebar: Conversation List -->
        <div class="col-lg-4 col-xl-3 mail-sidebar">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white">
                <div class="input-group input-group-sm rounded bg-light border">
                    <span class="input-group-text bg-transparent border-0 text-muted px-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <form method="get" class="w-75">
                        <input type="hidden" name="page" value="messages">
                        <input type="text" name="q" class="form-control bg-transparent border-0 shadow-none ps-0" placeholder="Search message" value="<?= Security::e($q) ?>">
                    </form>
                </div>
            </div>
            
            <div class="p-2 border-bottom bg-white d-flex justify-content-between align-items-center text-muted small fw-semibold">
                <span>All Messages</span>
                <span class="d-flex align-items-center gap-1 cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                    Newest
                </span>
            </div>

            <div class="mail-list bg-white">
                <?php if (empty($conversations)): ?>
                    <div class="text-center text-muted p-4 small mt-4">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2 opacity-50"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><br>
                        No messages found.
                    </div>
                <?php endif; ?>
                
                <?php foreach ($conversations as $conversation): ?>
                    <div class="mail-list-item <?= (int) $conversationId === (int) $conversation['id'] ? 'active' : '' ?>" onclick="window.location.href='index.php?page=messages&conversation_id=<?= (int) $conversation['id'] ?>'">
                        <div class="d-flex gap-3">
                            <!-- Avatar using initial of participant -->
                            <div class="mail-avatar bg-<?= ['primary','success','info','warning','danger'][crc32($conversation['participants']) % 5] ?> bg-opacity-25 text-<?= ['primary','success','info','warning','danger'][crc32($conversation['participants']) % 5] ?>">
                                <?= strtoupper(substr($conversation['participants'], 0, 1)) ?>
                            </div>
                            
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.95rem; color: #1a1a1a;">
                                        <?= Security::e($conversation['participants']) ?>
                                    </h6>
                                    <span class="small text-muted" style="font-size: 0.75rem;">
                                        <?php 
                                            $time = strtotime($conversation['last_message_at']);
                                            echo date('Y-m-d') == date('Y-m-d', $time) ? date('H:i', $time) : date('M d', $time);
                                        ?>
                                    </span>
                                </div>
                                <div class="fw-semibold text-dark text-truncate mb-1" style="font-size: 0.85rem;">
                                    <?= Security::e($conversation['subject']) ?>
                                </div>
                                <div class="small text-muted text-truncate" style="font-size: 0.85rem;">
                                    <?php if ((int) $conversation['unread_count'] > 0): ?>
                                        <span class="badge bg-primary rounded-pill me-1"><?= (int) $conversation['unread_count'] ?> new</span>
                                    <?php endif; ?>
                                    Click to view conversation...
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Area: Message Thread -->
        <div class="col-lg-8 col-xl-9 mail-content-area">
            <?php if ($conversationId): ?>
                <?php
                    // Find the active conversation subject
                    $activeSubject = 'Conversation';
                    foreach ($conversations as $c) {
                        if ((int)$c['id'] === (int)$conversationId) {
                            $activeSubject = $c['subject'];
                            break;
                        }
                    }
                ?>
                <!-- Thread Header -->
                <div class="p-3 p-lg-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark fs-5"><?= Security::e($activeSubject) ?></h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light text-muted border px-3">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Archive
                        </button>
                    </div>
                </div>
                
                <!-- Messages Scroll Area -->
                <div class="mail-thread">
                    <?php foreach ($messages as $message): ?>
                        <div class="mail-message-card">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="d-flex gap-3 align-items-center">
                                    <?php if (!empty($message['profile_picture'])): ?>
                                        <img src="storage/profiles/<?= Security::e($message['profile_picture']) ?>" alt="Avatar" class="rounded-circle" width="48" height="48" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="mail-avatar" style="width:48px; height:48px;">
                                            <?= strtoupper(substr($message['sender_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">
                                            <?= Security::e($message['sender_name']) ?> 
                                            <?php if ((int)$message['sender_id'] === (int)Auth::id()): ?>
                                                <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Me)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">Sent via ITOP System</div>
                                    </div>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2">
                                    <?= date('M d, Y, h:i A', strtotime($message['created_at'])) ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </div>
                            </div>
                            
                            <div class="message-body text-dark" style="font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap;"><?= Security::e($message['body']) ?></div>
                            
                            <?php if ($message['attachment_path']): ?>
                                <div class="mt-4 pt-3 border-top">
                                    <a href="storage/uploads/<?= Security::e($message['attachment_path']) ?>" download class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 py-2 px-3">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        Download Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($messages)): ?>
                        <div class="text-center text-muted mt-5 py-5">This conversation has no messages yet.</div>
                    <?php endif; ?>
                    
                    <!-- Inline Reply Box (like the reference) -->
                    <div class="mt-4">
                        <form method="post" action="index.php?page=send-message" enctype="multipart/form-data" data-ajax-form>
                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                            <input type="hidden" name="conversation_id" value="<?= (int) $conversationId ?>">
                            
                            <div class="reply-card">
                                <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2 text-muted small">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                                    Reply to <strong><?= Security::e($activeSubject) ?></strong>
                                </div>
                                
                                <textarea class="form-control p-3 w-100" name="body" placeholder="Write your reply here..." required></textarea>
                                
                                <div class="reply-toolbar">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-primary px-4 fw-semibold rounded-pill" type="submit">Send</button>
                                        <span class="text-muted small ms-2 d-none d-sm-inline">Press Cmd/Ctrl + Enter to send</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="attachment-input" class="custom-file-upload d-flex align-items-center gap-1">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                            <span class="small d-none d-md-inline">Attach file</span>
                                        </label>
                                        <input id="attachment-input" class="d-none" type="file" name="attachment" onchange="document.getElementById('attach-name-display').textContent = this.files[0]?.name || ''">
                                    </div>
                                </div>
                            </div>
                            <div id="attach-name-display" class="small text-muted mt-2 fw-semibold px-2"></div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="d-flex flex-column align-items-center justify-content-center h-100 p-5 text-center bg-transparent">
                    <img src="public/assets/img/centexs-logo-with-outline-1.png" alt="CENTEXS" height="60" class="mb-4 opacity-50 grayscale">
                    <h3 class="h4 fw-bold text-dark">Select an item to read</h3>
                    <p class="text-muted">Click on a message from the list on the left to read it here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold fs-6" id="composeModalLabel">New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form method="post" action="index.php?page=send-message" enctype="multipart/form-data" data-ajax-form>
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    
                    <div class="border-bottom px-3 py-2 d-flex align-items-center">
                        <label class="text-muted small me-2" style="width: 50px;">To:</label>
                        <select class="form-select border-0 shadow-none py-1 px-2" name="receiver_id" required>
                            <option value="">Select recipient...</option>
                            <?php foreach ($contacts as $contact): ?>
                                <option value="<?= (int) $contact['id'] ?>"><?= Security::e($contact['name']) ?> (<?= Security::e(ucwords(str_replace('-', ' ', $contact['role_slug']))) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="border-bottom px-3 py-2 d-flex align-items-center">
                        <label class="text-muted small me-2" style="width: 50px;">Subject:</label>
                        <input class="form-control border-0 shadow-none py-1 px-2" name="subject" placeholder="Enter subject line..." required>
                    </div>
                    
                    <div class="p-3">
                        <textarea class="form-control border-0 shadow-none" name="body" rows="10" placeholder="Write your message here..." required></textarea>
                    </div>
                    
                    <div class="bg-light border-top p-3 d-flex justify-content-between align-items-center rounded-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-primary px-4 fw-semibold rounded-pill" type="submit">Send Message</button>
                        </div>
                        <div>
                            <label for="modal-attachment" class="btn btn-outline-secondary btn-sm bg-white border">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                Attach File
                            </label>
                            <input id="modal-attachment" class="d-none" type="file" name="attachment" onchange="document.getElementById('modal-attach-name').textContent = this.files[0]?.name || ''">
                        </div>
                    </div>
                    <div id="modal-attach-name" class="small text-muted px-3 pb-3 bg-light text-end"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Move modal to body to avoid z-index/backdrop issues
        const modalEl = document.getElementById('composeModal');
        if (modalEl) {
            document.body.appendChild(modalEl);
        }

        const mailThread = document.querySelector('.mail-thread');
        if (mailThread) {
            // Scroll to bottom (the reply box) on load
            mailThread.scrollTop = mailThread.scrollHeight;
        }
        
        // Ctrl+Enter to submit reply
        const replyTextarea = document.querySelector('.reply-card textarea');
        if(replyTextarea) {
            replyTextarea.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
                }
            });
        }
    });
</script>
