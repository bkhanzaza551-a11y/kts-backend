{{-- Reusable Bootstrap Icon Picker Modal --}}
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="iconPickerModalLabel">Choose an Icon</h6>
                        <small class="text-secondary">Click any icon to select it for this category</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                {{-- Search & Filter Controls --}}
                <div class="mb-3">
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="iconSearchInput" class="form-control border-start-0 ps-0" placeholder="Search icons (e.g. chart, crypto, money, trend, video, book, signal, star)..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="iconClearSearch" style="display:none;"><i class="bi bi-x"></i></button>
                    </div>

                    {{-- Category Tabs --}}
                    <div class="d-flex flex-wrap gap-1" id="iconCategoryTabs">
                        <button type="button" class="btn btn-sm btn-primary icon-tab-btn active" data-filter="all">All (<span id="allIconsCount">0</span>)</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="trading">Trading & Signals</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="finance">Finance & Money</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="education">Education & Courses</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="analytics">Analytics & Charts</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="tech">Tech & System</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary icon-tab-btn" data-filter="badges">Badges & Misc</button>
                    </div>
                </div>

                {{-- Icons Grid Container --}}
                <div class="icon-picker-grid-container p-2 bg-light rounded border" style="max-height: 420px; overflow-y: auto;">
                    <div class="row g-2" id="iconGrid">
                        {{-- Populated dynamically via JS --}}
                    </div>
                    <div id="noIconsFound" class="text-center py-5 d-none">
                        <i class="bi bi-emoji-neutral fs-1 text-muted d-block mb-2"></i>
                        <p class="text-secondary fw-medium mb-1">No matching icons found</p>
                        <small class="text-muted">Try a different search term or check custom names.</small>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    <span id="selectedIconLabel">Selected: <strong class="text-primary font-monospace" id="currentSelectedIconText">None</strong></span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.icon-select-card {
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 6px;
    text-align: center;
    user-select: none;
    height: 100%;
}
.icon-select-card:hover {
    border-color: #0d6efd;
    background: #f0f7ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.12);
}
.icon-select-card.active-selected {
    border-color: #0d6efd;
    background: #e7f1ff;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}
.icon-select-card i {
    font-size: 1.6rem;
    color: #334155;
    transition: color 0.15s ease;
}
.icon-select-card:hover i,
.icon-select-card.active-selected i {
    color: #0d6efd;
}
.icon-select-card .icon-name {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}
.icon-select-card:hover .icon-name {
    color: #0f172a;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconLibrary = [
        // Trading & Signals
        { name: 'graph-up', tags: 'chart profit bullish growth up trend market trade signal', cat: 'trading' },
        { name: 'graph-up-arrow', tags: 'chart profit up green surge trend trade signal', cat: 'trading' },
        { name: 'graph-down', tags: 'chart loss bearish drop down trend short sell signal', cat: 'trading' },
        { name: 'graph-down-arrow', tags: 'chart drop down loss red sell short signal', cat: 'trading' },
        { name: 'activity', tags: 'pulse market heartbeat trend volatility signal action', cat: 'trading' },
        { name: 'broadcast', tags: 'signal radio live broadcast transmission alert alert', cat: 'trading' },
        { name: 'broadcast-pin', tags: 'signal live pin trade alert channel', cat: 'trading' },
        { name: 'wifi', tags: 'signal connection wireless stream live online', cat: 'trading' },
        { name: 'reception-4', tags: 'signal strength full bars network strong', cat: 'trading' },
        { name: 'lightning', tags: 'fast instant flash scalping speed quick signal', cat: 'trading' },
        { name: 'lightning-charge', tags: 'energy power fast execution turbo boost', cat: 'trading' },
        { name: 'lightning-fill', tags: 'flash power fast instant vip turbo alert', cat: 'trading' },
        { name: 'bell', tags: 'notification alert signal reminder ring alarm', cat: 'trading' },
        { name: 'bell-fill', tags: 'notification active alert signal priority', cat: 'trading' },
        { name: 'bullseye', tags: 'target take profit accuracy tp goal precision', cat: 'trading' },
        { name: 'crosshair', tags: 'target sniper entry stop loss aim precision precision', cat: 'trading' },
        { name: 'compass', tags: 'direction guide trend strategy navigation compass', cat: 'trading' },
        { name: 'speedometer', tags: 'gauge speed volatility meter risk momentum', cat: 'trading' },
        { name: 'speedometer2', tags: 'gauge performance speed risk level momentum', cat: 'trading' },
        { name: 'arrow-up-right-circle', tags: 'buy long up bullish profit call enter', cat: 'trading' },
        { name: 'arrow-down-right-circle', tags: 'sell short down bearish put exit', cat: 'trading' },
        { name: 'arrow-repeat', tags: 'repeat recurring trade auto cycle swap loop', cat: 'trading' },
        { name: 'shuffle', tags: 'hedge order flow mix arbitrage switch', cat: 'trading' },

        // Finance & Money
        { name: 'currency-dollar', tags: 'dollar usd money cash currency us forex profit', cat: 'finance' },
        { name: 'currency-euro', tags: 'euro eur money cash currency europe forex', cat: 'finance' },
        { name: 'currency-pound', tags: 'pound gbp money cash currency british forex', cat: 'finance' },
        { name: 'currency-yen', tags: 'yen jpy money cash currency japan forex', cat: 'finance' },
        { name: 'currency-bitcoin', tags: 'bitcoin btc crypto coin blockchain token satoshi', cat: 'finance' },
        { name: 'currency-exchange', tags: 'forex exchange swap transfer currency fx convert', cat: 'finance' },
        { name: 'cash', tags: 'cash money bill payout balance dollar capital', cat: 'finance' },
        { name: 'cash-coin', tags: 'money coins payment profit income payout earnings', cat: 'finance' },
        { name: 'cash-stack', tags: 'money rich profits capital investment earnings funds', cat: 'finance' },
        { name: 'coin', tags: 'coin gold token crypto crypto single currency', cat: 'finance' },
        { name: 'credit-card', tags: 'card payment deposit bank balance visa debit', cat: 'finance' },
        { name: 'credit-card-2-front', tags: 'payment vip plan subscribe account card', cat: 'finance' },
        { name: 'wallet', tags: 'wallet funds balance capital portfolio account assets', cat: 'finance' },
        { name: 'wallet2', tags: 'wallet money bag crypto assets funds', cat: 'finance' },
        { name: 'bank', tags: 'bank institutional finance broker liquidity funds wire', cat: 'finance' },
        { name: 'piggy-bank', tags: 'savings investment safe growth capital profit', cat: 'finance' },
        { name: 'safe', tags: 'vault security funds protection safe locked reserve', cat: 'finance' },
        { name: 'receipt', tags: 'invoice bill transaction order report history receipt', cat: 'finance' },
        { name: 'calculator', tags: 'calculate risk lot size margin pips calculator math', cat: 'finance' },
        { name: 'gem', tags: 'diamond premium vip jewel value luxury gem wealth', cat: 'finance' },

        // Education & Courses
        { name: 'book', tags: 'book course learning guide education study reading', cat: 'education' },
        { name: 'book-half', tags: 'book open chapter reading module learning tutorial', cat: 'education' },
        { name: 'journal-text', tags: 'journal log notes article trading journal diary guide', cat: 'education' },
        { name: 'journal-bookmark', tags: 'bookmark saved favorite lesson guide module', cat: 'education' },
        { name: 'mortarboard', tags: 'graduation university academy academy degree certified master education', cat: 'education' },
        { name: 'mortarboard-fill', tags: 'academy education diploma expert pro certified', cat: 'education' },
        { name: 'easel', tags: 'whiteboard teaching webinar lesson strategy chart classroom', cat: 'education' },
        { name: 'easel2', tags: 'presentation webinar tutorial training class', cat: 'education' },
        { name: 'play-circle', tags: 'video video lesson webinar watch stream player play tutorial', cat: 'education' },
        { name: 'play-btn', tags: 'video button course media stream watch start', cat: 'education' },
        { name: 'camera-video', tags: 'live stream webinar recording camera video lesson', cat: 'education' },
        { name: 'collection-play', tags: 'playlist series course bundle videos library', cat: 'education' },
        { name: 'file-earmark-play', tags: 'video file media document attachment lesson', cat: 'education' },
        { name: 'lightbulb', tags: 'idea tip strategy secret insight advice knowhow', cat: 'education' },
        { name: 'lightbulb-fill', tags: 'idea glow bright tip innovation smart brain', cat: 'education' },
        { name: 'puzzle', tags: 'strategy combination basics fundamentals parts guide', cat: 'education' },
        { name: 'question-circle', tags: 'faq help beginner questions ask support info', cat: 'education' },
        { name: 'info-circle', tags: 'details overview facts guide info help information', cat: 'education' },
        { name: 'chat-left-quote', tags: 'quote quote discussion testimonial review notes', cat: 'education' },
        { name: 'file-text', tags: 'document pdf notes summary cheat sheet rules', cat: 'education' },

        // Analytics & Charts
        { name: 'bar-chart', tags: 'histogram statistics volume analysis bars report', cat: 'analytics' },
        { name: 'bar-chart-fill', tags: 'bars analytics performance metrics volume report', cat: 'analytics' },
        { name: 'bar-chart-line', tags: 'indicator line chart technical analytics report', cat: 'analytics' },
        { name: 'bar-chart-line-fill', tags: 'analytics indicators metrics evaluation bars', cat: 'analytics' },
        { name: 'pie-chart', tags: 'pie chart portfolio distribution allocation assets', cat: 'analytics' },
        { name: 'pie-chart-fill', tags: 'portfolio pie distribution breakdown share', cat: 'analytics' },
        { name: 'diagram-2', tags: 'flowchart hierarchy strategy decision tree algorithm', cat: 'analytics' },
        { name: 'diagram-3', tags: 'network tree structure architecture flowchart system', cat: 'analytics' },
        { name: 'bezier2', tags: 'curve pattern technical harmonic waves fibonacci', cat: 'analytics' },
        { name: 'sliders', tags: 'filters settings tune parameters custom risk', cat: 'analytics' },
        { name: 'toggles', tags: 'switches config control options preferences', cat: 'analytics' },
        { name: 'clipboard-data', tags: 'backtest logs audit audit data stats results', cat: 'analytics' },
        { name: 'table', tags: 'grid spreadsheet data matrix numbers results', cat: 'analytics' },

        // Tech & System
        { name: 'robot', tags: 'bot ea algorithmic trading mt5 automated ai expert advisor', cat: 'tech' },
        { name: 'cpu', tags: 'processor computation algorithm engine server automation', cat: 'tech' },
        { name: 'gear', tags: 'settings configuration tools maintenance options', cat: 'tech' },
        { name: 'gear-wide-connected', tags: 'api integration connected network hooks automation', cat: 'tech' },
        { name: 'shield-check', tags: 'verified secure protected safe verified compliance', cat: 'tech' },
        { name: 'shield-lock', tags: 'security lock authentication encrypted private', cat: 'tech' },
        { name: 'shield-shaded', tags: 'defense risk management stop protection security', cat: 'tech' },
        { name: 'lock', tags: 'private exclusive vip restricted closed locked', cat: 'tech' },
        { name: 'unlock', tags: 'public open free unlocked accessible access', cat: 'tech' },
        { name: 'key', tags: 'api key license pass secret credential token', cat: 'tech' },
        { name: 'cloud-arrow-up', tags: 'upload server sync cloud backup data', cat: 'tech' },
        { name: 'cloud-arrow-down', tags: 'download bot file download assets install', cat: 'tech' },
        { name: 'terminal', tags: 'command code console developer logs script', cat: 'tech' },

        // Badges & Misc
        { name: 'trophy', tags: 'winner leader champion award gold rank contest', cat: 'badges' },
        { name: 'trophy-fill', tags: 'champion trophy gold top trader success', cat: 'badges' },
        { name: 'award', tags: 'badge ribbon medal honor certified top achievement', cat: 'badges' },
        { name: 'award-fill', tags: 'medal badge top certified verified winner', cat: 'badges' },
        { name: 'star', tags: 'favorite vip top rating highlight special star', cat: 'badges' },
        { name: 'star-fill', tags: 'favorite rated top premium vip gold star', cat: 'badges' },
        { name: 'fire', tags: 'hot trending popular top streak winning streak fire', cat: 'badges' },
        { name: 'heart', tags: 'favorite like community love appreciation popular', cat: 'badges' },
        { name: 'rocket', tags: 'launch moon fast pump growth rocket boost', cat: 'badges' },
        { name: 'rocket-takeoff', tags: 'launch pump surge takeoff explosive win', cat: 'badges' },
        { name: 'tag', tags: 'tag label category price ticket signal tag', cat: 'badges' },
        { name: 'tags', tags: 'categories tags multiple labels classification', cat: 'badges' },
        { name: 'flag', tags: 'target milestone goal flag indicator marker', cat: 'badges' },
        { name: 'check-circle', tags: 'success completed approved verified passed done', cat: 'badges' },
        { name: 'check2-circle', tags: 'done completed green checkmark success passed', cat: 'badges' },
        { name: 'bookmark-check', tags: 'saved marked bookmark passed completed done', cat: 'badges' },
        { name: 'people', tags: 'community members traders social group room', cat: 'badges' },
        { name: 'chat-dots', tags: 'chat conversation discussion talk message message', cat: 'badges' },
        { name: 'globe', tags: 'global world international markets forex world', cat: 'badges' },
        { name: 'globe2', tags: 'global internet worldwide international planet', cat: 'badges' },
    ];

    const iconInput = document.getElementById('iconInput') || document.querySelector('input[name="icon"]');
    const previewIcon = document.getElementById('iconPreviewIcon');
    const colorPicker = document.getElementById('colorPicker') || document.querySelector('input[name="color"]');
    const colorText = document.getElementById('colorText');
    const iconGrid = document.getElementById('iconGrid');
    const searchInput = document.getElementById('iconSearchInput');
    const clearSearchBtn = document.getElementById('iconClearSearch');
    const noIconsFound = document.getElementById('noIconsFound');
    const currentSelectedIconText = document.getElementById('currentSelectedIconText');
    const allIconsCount = document.getElementById('allIconsCount');
    const tabButtons = document.querySelectorAll('.icon-tab-btn');

    if (allIconsCount) {
        allIconsCount.textContent = iconLibrary.length;
    }

    function cleanIconName(name) {
        if (!name) return '';
        name = name.trim().toLowerCase();
        if (name.startsWith('bi-')) {
            name = name.substring(3);
        }
        return name;
    }

    function updateLivePreview(iconName) {
        const cleaned = cleanIconName(iconName) || 'graph-up';
        if (previewIcon) {
            previewIcon.className = 'bi bi-' + cleaned + ' fs-5';
            if (colorPicker) {
                previewIcon.style.color = colorPicker.value;
            }
        }
        if (currentSelectedIconText) {
            currentSelectedIconText.textContent = cleaned;
        }
        // Highlight active card in grid
        document.querySelectorAll('.icon-select-card').forEach(card => {
            if (card.dataset.icon === cleaned) {
                card.classList.add('active-selected');
            } else {
                card.classList.remove('active-selected');
            }
        });
    }

    function renderIcons(filterCategory = 'all', searchQuery = '') {
        if (!iconGrid) return;
        iconGrid.innerHTML = '';
        const query = searchQuery.trim().toLowerCase();
        let matchCount = 0;
        const currentIcon = cleanIconName(iconInput ? iconInput.value : '');

        iconLibrary.forEach(item => {
            const matchesCat = filterCategory === 'all' || item.cat === filterCategory;
            const matchesSearch = !query || 
                item.name.toLowerCase().includes(query) || 
                item.tags.toLowerCase().includes(query) ||
                item.cat.toLowerCase().includes(query);

            if (matchesCat && matchesSearch) {
                matchCount++;
                const isSelected = item.name === currentIcon;
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
                col.innerHTML = `
                    <div class="icon-select-card ${isSelected ? 'active-selected' : ''}" data-icon="${item.name}" title="${item.name}">
                        <i class="bi bi-${item.name}"></i>
                        <span class="icon-name">${item.name}</span>
                    </div>
                `;

                col.querySelector('.icon-select-card').addEventListener('click', function() {
                    const selectedName = this.dataset.icon;
                    if (iconInput) {
                        iconInput.value = selectedName;
                        iconInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    updateLivePreview(selectedName);

                    // Close modal
                    const modalEl = document.getElementById('iconPickerModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });

                iconGrid.appendChild(col);
            }
        });

        if (noIconsFound) {
            if (matchCount === 0) {
                noIconsFound.classList.remove('d-none');
            } else {
                noIconsFound.classList.add('d-none');
            }
        }
    }

    // Initial render
    renderIcons('all', '');
    if (iconInput) {
        updateLivePreview(iconInput.value);
        iconInput.addEventListener('input', function() {
            const clean = cleanIconName(this.value);
            this.value = clean;
            updateLivePreview(clean);
        });
    }

    // Color sync
    if (colorPicker) {
        colorPicker.addEventListener('input', function() {
            if (previewIcon) previewIcon.style.color = this.value;
            if (colorText) colorText.value = this.value;
        });
    }
    if (colorText) {
        colorText.addEventListener('input', function() {
            if (colorPicker) colorPicker.value = this.value;
            if (previewIcon) previewIcon.style.color = this.value;
        });
    }

    // Search events
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const activeTab = document.querySelector('.icon-tab-btn.active');
            const cat = activeTab ? activeTab.dataset.filter : 'all';
            if (clearSearchBtn) {
                clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
            }
            renderIcons(cat, this.value);
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                this.style.display = 'none';
                const activeTab = document.querySelector('.icon-tab-btn.active');
                renderIcons(activeTab ? activeTab.dataset.filter : 'all', '');
            }
        });
    }

    // Tab events
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-outline-secondary');

            const cat = this.dataset.filter;
            const query = searchInput ? searchInput.value : '';
            renderIcons(cat, query);
        });
    });

    // When modal opens, refresh selection
    const modalEl = document.getElementById('iconPickerModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            if (searchInput) {
                searchInput.focus();
            }
            if (iconInput) {
                updateLivePreview(iconInput.value);
            }
        });
    }
});
</script>
