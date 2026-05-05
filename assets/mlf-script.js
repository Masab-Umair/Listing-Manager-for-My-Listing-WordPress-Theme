
/**
 * Listing Manager Pro - Main JavaScript
 */

/**
 * Render a field value: images → <img>, URLs → <a>, multiline → <br> separated
 */
function mlfRenderValue(key, value) {
    var socialIcon=function(name){name=(name||'').toLowerCase(); var map={facebook:'bi bi-facebook',instagram:'bi bi-instagram',youtube:'bi bi-youtube',linkedin:'bi bi-linkedin',twitter:'bi bi-twitter-x',x:'bi bi-twitter-x',tiktok:'bi bi-tiktok',website:'bi bi-globe'}; return map[name]||'bi bi-link-45deg';};
    if (!value) return '';
    var html = '';

    var isImg = function(v) {
        if (typeof v !== 'string') return false;
        if (v.match(/\.(jpg|jpeg|png|webp|gif|svg)(\?.*)?$/i)) return true;
        if (v.match(/wp-content\/uploads/i)) return true;
        return false;
    };

    var isLink = function(v) {
        return typeof v === 'string' && /(https?:\/\/|www\.)/i.test(v);
    };

    var normalizeSocialHref = function(network, url) {
        if (!url) return '';
        url = url.trim();
        if (!url) return '';

        if (/^(https?:\/\/|\/\/)/i.test(url)) {
            if (/^\/\//.test(url)) {
                return 'https:' + url;
            }
            return url;
        }

        if (/^[^@\s\/]+\.[^@\s\/]+/.test(url)) {
            return 'https://' + url;
        }

        url = url.replace(/^@/, '').replace(/^\//, '');
        var net = (network || '').toLowerCase();
        switch (net) {
            case 'facebook':
                return url.match(/^(facebook\.com|m\.facebook\.com|www\.facebook\.com)/i) ? 'https://' + url : 'https://www.facebook.com/' + url;
            case 'instagram':
                return url.match(/^(instagram\.com|www\.instagram\.com)/i) ? 'https://' + url : 'https://www.instagram.com/' + url;
            case 'youtube':
                return url.match(/^(youtube\.com|youtu\.be)/i) ? 'https://' + url : 'https://www.youtube.com/' + url;
            case 'linkedin':
                return url.match(/^(linkedin\.com|www\.linkedin\.com)/i) ? 'https://' + url : 'https://www.linkedin.com/in/' + url;
            case 'twitter':
            case 'x':
                return url.match(/^(twitter\.com|x\.com|www\.twitter\.com|www\.x\.com)/i) ? 'https://' + url : 'https://twitter.com/' + url;
            case 'tiktok':
                return url.match(/^(tiktok\.com|www\.tiktok\.com)/i) ? 'https://' + url : 'https://www.tiktok.com/@' + url;
            case 'website':
                return 'https://' + url;
            default:
                return 'https://' + url;
        }
    };

    var renderSingle = function(v) {
        v = v.trim();
        if (!v) return '';
        if (isImg(v)) {
            return '<img src="' + v + '" class="thumb" alt="Image" style="max-width:120px;max-height:120px;object-fit:cover;border-radius:6px;margin:4px;display:inline-block;vertical-align:middle;" />';
        }
        if (typeof v==='string' && v.indexOf(':')>-1 && key==='links'){
            var parts=v.split(':');
            var net=parts.shift().trim();
            var url=parts.join(':').trim();
            if(url){
                var href = normalizeSocialHref(net, url);
                return '<a class="mlf-social-link" href="'+href+'" target="_blank" rel="noopener noreferrer"><i class="'+socialIcon(net)+'"></i> '+net+'</a>';
            }
        }
        if ((key === 'links' || key === 'url' || key === 'job_website' || key.indexOf('website') > -1) && isLink(v)) {
            var href=v.match(/^https?:/i)?v:'https://'+v;
            return '<a href="' + href + '" target="_blank" rel="noopener noreferrer">' + v + '</a>';
        }
        return v;
    };

    if (Array.isArray(value)) {
        value.forEach(function(v) { html += renderSingle(v) + '<br/>'; });
    } else if (typeof value === 'string' && value.indexOf('\n') > -1) {
        value.split('\n').forEach(function(v) {
            var part = v.trim();
            if (part) html += renderSingle(part) + '<br/>';
        });
    } else if (typeof value === 'string' && value.indexOf(',') > -1) {
        var parts = value.split(',').map(function(s) { return s.trim(); });
        var allLinks = parts.filter(function(p) { return p !== ''; }).every(function(p) { return isLink(p); });
        if (allLinks) {
            parts.forEach(function(v) { if (v) html += renderSingle(v) + '<br/>'; });
        } else {
            html += renderSingle(value);
        }
    } else {
        html += renderSingle(value);
    }
    return html;
}

(function($) {
    'use strict';

    // ─── Open Detail Modal ───────────────────────────────────────────────────────
    window.mlfOpenDetail = function(id) {
        var modal = document.getElementById('mlf-detail-modal');
        var body  = document.getElementById('mlf-modal-body');
        var title = document.getElementById('mlf-modal-title');

        if (!modal || !body || !title) {
            console.error('MLF: Modal elements not found');
            return;
        }

        body.innerHTML = '<div class="mlf-loading"><div class="mlf-spinner"></div></div>';
        modal.classList.add('active');

        $.ajax({
            url: mlf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'mlf_get_detail',
                id: id,
                nonce: mlf_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    title.textContent = data.title;

                    var html = '<div class="mlf-back-btn" onclick="mlfCloseModal()">&#8592; Back to listings</div>';

                    // Basic Info Section
                    html += '<div class="mlf-detail-section">';
                    html += '<h4>Basic Information</h4>';
                    html += '<div class="mlf-detail-grid">';
                    html += '<div class="mlf-detail-item"><label>Name</label><span>' + data.title + '</span></div>';
                    html += '<div class="mlf-detail-item"><label>Status</label><span class="status-badge ' + data.status_class + '">' + data.status_label + '</span></div>';
                    html += '<div class="mlf-detail-item"><label>Date</label><span>' + data.date + '</span></div>';
                    html += '<div class="mlf-detail-item"><label>ID</label><span>#' + data.id + '</span></div>';
                    html += '</div></div>';

                    // Organized Sections
                    if (data.sections && Object.keys(data.sections).length > 0) {
                        for (var sectionName in data.sections) {
                            var sectionTitle = sectionName
                                .replace(/-/g, ' ')
                                .replace(/_/g, ' ')
                                .replace(/\b\w/g, function(l) { return l.toUpperCase(); });

                            html += '<div class="mlf-detail-section">';
                            html += '<h4>' + sectionTitle + '</h4>';
                            html += '<div class="mlf-detail-grid">';

                            var section = data.sections[sectionName];
                            for (var key in section) {
                                var value = section[key];
                                if (value && value !== '') {
                                    var label = (data.labels && data.labels[key])
                                        ? data.labels[key]
                                        : key.replace(/-/g, ' ').replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

                                    html += '<div class="mlf-detail-item"><label>' + label + '</label><span>' + mlfRenderValue(key, value) + '</span></div>';
                                }
                            }

                            html += '</div></div>';
                        }
                    } else if (data.meta && Object.keys(data.meta).length > 0) {
                        html += '<div class="mlf-detail-section">';
                        html += '<h4>Additional Details</h4>';
                        html += '<div class="mlf-detail-grid">';

                        for (var k in data.meta) {
                            var mv = data.meta[k];
                            if (mv && mv !== '') {
                                var mlbl = (data.labels && data.labels[k])
                                    ? data.labels[k]
                                    : k.replace(/-/g, ' ').replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                                html += '<div class="mlf-detail-item"><label>' + mlbl + '</label><span>' + mlfRenderValue(k, mv) + '</span></div>';
                            }
                        }

                        html += '</div></div>';
                    }

                    // ── Quick Action Buttons ──────────────────────────────────────
                    html += '<div class="mlf-card-detail-actions" style="margin-top:20px;padding-top:20px;border-top:1px solid #eee;">';
                    html += '<h4>Quick Actions</h4>';
                    html += '<div style="display:flex;gap:10px;flex-wrap:wrap;">';
                    html += '<button class="mlf-btn mlf-btn-primary" onclick="mlfEdit(' + id + ')">&#9999;&#65039; Edit</button>';
                    if (data.post_status !== 'publish') {
                        html += '<button class="mlf-btn mlf-btn-success" onclick="mlfAction(' + id + ', \'approve\')">&#10003; Approve</button>';
                    }
                    if (data.post_status !== 'draft') {
                        html += '<button class="mlf-btn mlf-btn-secondary" onclick="mlfAction(' + id + ', \'reject\')">&#10007; Reject</button>';
                    }
                    html += '<button class="mlf-btn mlf-btn-danger" onclick="mlfAction(' + id + ', \'trash\')">&#128465; Trash</button>';
                    html += '</div></div>';

                    body.innerHTML = html;
                } else {
                    body.innerHTML = '<div class="mlf-error">Error: ' + (response.data || 'Unknown error') + '</div>';
                }
            },
            error: function() {
                body.innerHTML = '<div class="mlf-error">Failed to load listing details. Please login to your admin account.</div>';
            }
        });
    };

    // ─── Close Modal ─────────────────────────────────────────────────────────────
    window.mlfCloseModal = function() {
        var modal = document.getElementById('mlf-detail-modal');
        if (modal) modal.classList.remove('active');
    };

    // ─── Modal Action (approve / reject / trash) from detail view ────────────────
    window.mlfAction = function(id, type) {
        if (!confirm('Are you sure you want to ' + type + ' this listing?')) return;

        $.ajax({
            url: mlf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'mlf_action',
                id: id,
                type: type,
                nonce: mlf_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    mlfCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Failed to perform action. Please try again.');
            }
        });
    };

    // ─── Card Action Buttons (approve / reject / trash on listing card) ───────────
    window.mlfCardAction = function(id, type) {
        if (!confirm('Are you sure you want to ' + type + ' this listing?')) return;

        $.ajax({
            url: mlf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'mlf_action',
                id: id,
                type: type,
                nonce: mlf_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Failed to perform action. Please try again.');
            }
        });
    };

    // ─── Edit Listing ─────────────────────────────────────────────────────────────
    window.mlfEdit = function(id) {
        var body  = document.getElementById('mlf-modal-body');
        var title = document.getElementById('mlf-modal-title');

        title.textContent = 'Edit Listing';
        body.innerHTML = '<div class="mlf-loading"><div class="mlf-spinner"></div></div>';

        $.ajax({
            url: mlf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'mlf_get_detail',
                id: id,
                nonce: mlf_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data   = response.data;
                    var labels = data.labels || {};

                    var html = '<div class="mlf-back-btn" onclick="mlfOpenDetail(' + id + ')">&#8592; Back to details</div>';
                    html += '<form id="mlf-edit-form">';
                    html += '<input type="hidden" name="id" value="' + id + '">';

                    var longKeys = ['description','bio','why','focus','idea','collaboration',
                        'influence','awards','content','services','year','initial-appointment',
                        'follow-up','3rd-party','online-booking','confidentiality','waiting-list',
                        'one','two','three','four','five'];

                    for (var key in data.meta) {
                        if (key === '_edit_lock' || key === '_edit_last') continue;

                        var value = data.meta[key] || '';
                        var label = labels[key] || key.replace(/-/g, ' ').replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                        var isLong = longKeys.some(function(t) { return key.indexOf(t) > -1; });

                        html += '<div class="mlf-edit-field"><label>' + label + '</label>';

                        if (isLong) {
                            html += '<textarea name="' + key + '" rows="4">' + value + '</textarea>';
                        } else if (key.indexOf('email') > -1) {
                            html += '<input type="email" name="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" class="mlf-edit-input">';
                        } else if (key.indexOf('phone') > -1) {
                            html += '<input type="tel" name="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" class="mlf-edit-input">';
                        } else if (key.indexOf('url') > -1 || key === 'job_website') {
                            html += '<input type="url" name="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" class="mlf-edit-input">';
                        } else {
                            html += '<input type="text" name="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" class="mlf-edit-input">';
                        }

                        html += '</div>';
                    }

                    html += '<div class="mlf-edit-actions">';
                    html += '<button type="button" class="mlf-btn mlf-btn-success" onclick="mlfSaveEdit(' + id + ')">&#128190; Save Changes</button>';
                    html += '<button type="button" class="mlf-btn mlf-btn-secondary" onclick="mlfOpenDetail(' + id + ')">Cancel</button>';
                    html += '</div></form>';

                    body.innerHTML = html;
                }
            }
        });
    };

    // ─── Save Edited Data ─────────────────────────────────────────────────────────
    window.mlfSaveEdit = function(id) {
        var form     = document.getElementById('mlf-edit-form');
        var formData = new FormData(form);
        formData.append('action', 'mlf_save_edit');
        formData.append('id', id);
        formData.append('nonce', mlf_vars.nonce);

        $.ajax({
            url: mlf_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Changes saved successfully!');
                    mlfOpenDetail(id);
                } else {
                    alert('Error: ' + (response.data || 'Could not save changes'));
                }
            },
            error: function() {
                alert('Failed to save changes. Please try again.');
            }
        });
    };

    // ─── Event: close modal on overlay click ─────────────────────────────────────
    $(document).on('click', '#mlf-detail-modal', function(e) {
        if (e.target === this) mlfCloseModal();
    });

    // ─── Event: close modal on Escape key ────────────────────────────────────────
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') mlfCloseModal();
    });

    // ─── Event: keep anchor clicks inside a card from triggering card click behavior ─
    $(document).on('click', '.mlf-elementor-card a, .mlf-listing-card a, .mlf-user-card a', function(e) {
        e.stopPropagation();
    });

    // ─── Event: open detail on card click (exclude action buttons and links) ─────
    $(document).on('click', '.mlf-elementor-card, .mlf-listing-card, .mlf-user-card', function(e) {
        if ($(e.target).closest('a, .card-actions, .mlf-card-actions').length) return;
        e.preventDefault();
        var id = $(this).data('id');
        if (id) mlfOpenDetail(id);
    });

})(jQuery);
