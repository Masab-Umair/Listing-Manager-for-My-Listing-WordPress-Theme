
/**
 * Listing Manager Pro - Main JavaScript
 */

/**
 * Render a field value: images → <img>, URLs → <a>, multiline → <br> separated
 */
function mlfRenderValue(key, value) {
    // work_hours arrives pre-rendered with HTML from PHP
    if (key === 'work_hours') {
        return value || '';
    }
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

function mlfEscapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function mlfNormalizeListValue(value) {
    if (Array.isArray(value)) return value.map(String);
    if (value == null || value === '') return [];
    return String(value).split(/,\s*|\n/).map(function(item) {
        return item.trim();
    }).filter(Boolean);
}

function mlfOptionsToArray(options) {
    if (!options) return [];
    if (Array.isArray(options)) return options.map(function(option) {
        if (typeof option === 'object') {
            return {
                value: option.value || option.key || option.label || '',
                label: option.label || option.value || option.key || ''
            };
        }
        return { value: option, label: option };
    });
    return Object.keys(options).map(function(key) {
        return { value: key, label: options[key] };
    });
}

function mlfSlugify(value) {
    return String(value == null ? '' : value)
        .toLowerCase()
        .replace(/&amp;/g, 'and')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function mlfOptionSelected(option, values) {
    var candidates = [
        String(option.value),
        String(option.label),
        mlfSlugify(option.value),
        mlfSlugify(option.label)
    ];
    
    return values.some(function(value) {
        value = String(value);
        return candidates.indexOf(value) > -1 || candidates.indexOf(mlfSlugify(value)) > -1;
    });
}

function mlfFieldType(field) {
    var type = (field && field.type ? field.type : 'text').toLowerCase();
    if (type === 'texteditor' || type === 'wp-editor' || type === 'paragraph') return 'textarea';
    if (type === 'term-select') {
        return field && (field['terms-template'] === 'checklist') ? 'checkbox' : 'multiselect';
    }
    if (type === 'fileupload') return 'file';
    if (type === 'work-hours') return 'work-hours';
    if (type === 'links') return 'links';
    if (type === 'related-listing') return 'text';
    return type;
}

function mlfRenderMediaPreview(key, value, multiple) {
    var values = mlfNormalizeListValue(value);
    var preview = '';
    values.forEach(function(item) {
        if (/\.(jpg|jpeg|png|webp|gif|svg)(\?.*)?$/i.test(item) || /wp-content\/uploads/i.test(item)) {
            preview += '<img src="' + mlfEscapeHtml(item) + '" alt="" class="mlf-edit-preview-image">';
        } else if (item) {
            preview += '<a href="' + mlfEscapeHtml(item) + '" target="_blank" rel="noopener noreferrer">' + mlfEscapeHtml(item) + '</a>';
        }
    });

    var accept = key === 'crimimal-records-check' || key === 'content-upload' ? '' : ' accept="image/*"';
    var fileInput = '<input type="file" class="mlf-edit-input mlf-file-input"' + accept + (multiple ? ' multiple' : '') + '>';
    var hiddenValue = '<input type="hidden" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(values.join(', ')) + '">';

    if (multiple) {
        return '<div class="mlf-media-preview">' + preview + '</div>' + hiddenValue + fileInput;
    }

    return '<div class="mlf-media-preview">' + preview + '</div>' + hiddenValue + fileInput;
}

function mlfRenderWorkHours(key, value) {
    var dayLabels = {
        monday: 'Monday',
        tuesday: 'Tuesday',
        wednesday: 'Wednesday',
        thursday: 'Thursday',
        friday: 'Friday',
        saturday: 'Saturday',
        sunday: 'Sunday'
    };
    var hours = {};
    
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        hours = value;
    }
    
    var html = '<input type="hidden" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(JSON.stringify(hours)) + '" data-mlf-work-hours-value>';
    html += '<div class="mlf-work-hours-editor">';
    html += '<label class="mlf-work-hours-timezone">Timezone <input type="text" class="mlf-edit-input" data-mlf-work-hours-timezone value="' + mlfEscapeHtml(hours.timezone || '') + '" placeholder="America/Vancouver"></label>';
    html += '<div class="mlf-work-hours-grid">';
    
    Object.keys(dayLabels).forEach(function(day) {
        var dayData = hours[day] || hours[dayLabels[day]] || {};
        var status = dayData.status || 'closed';
        if (status === 'open') status = 'enter-hours';
        
        var slot = {};
        if (dayData.hours && dayData.hours.length) {
            slot = dayData.hours[0] || {};
        } else {
            slot = dayData;
        }
        
        html += '<div class="mlf-work-hours-row" data-mlf-work-hours-day="' + day + '">';
        html += '<strong>' + dayLabels[day] + '</strong>';
        html += '<select class="mlf-edit-input" data-mlf-work-hours-status>';
        [
            { value: 'enter-hours', label: 'Enter Hours' },
            { value: 'by-appointment-only', label: 'By Appointment Only' },
            { value: 'closed', label: 'Closed' }
        ].forEach(function(option) {
            html += '<option value="' + option.value + '"' + (status === option.value ? ' selected' : '') + '>' + option.label + '</option>';
        });
        html += '</select>';
        html += '<input type="time" class="mlf-edit-input" data-mlf-work-hours-from value="' + mlfEscapeHtml(slot.from || '') + '">';
        html += '<input type="time" class="mlf-edit-input" data-mlf-work-hours-to value="' + mlfEscapeHtml(slot.to || '') + '">';
        html += '</div>';
    });
    
    html += '</div>';
    html += '</div>';
    return html;
}

function mlfRenderLinksField(key, value) {
    var values = mlfNormalizeListValue(value);
    if (!values.length) values = [''];
    var html = '<input type="hidden" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(values.join(', ')) + '" data-mlf-links-value>';
    html += '<div class="mlf-repeatable-list" data-mlf-links-list>';
    values.forEach(function(item) {
        var network = '';
        var url = item;
        if (/^[a-z0-9_-]+:/i.test(item) && !/^https?:\/\//i.test(item)) {
            var parts = item.split(':');
            network = parts.shift();
            url = parts.join(':');
        }
        html += '<div class="mlf-repeatable-row">' +
            '<input type="text" value="' + mlfEscapeHtml(network) + '" placeholder="Network" data-mlf-link-network class="mlf-edit-input">' +
            '<input type="url" value="' + mlfEscapeHtml(url) + '" placeholder="URL" data-mlf-link-url class="mlf-edit-input">' +
            '<button type="button" class="mlf-small-btn" data-mlf-remove-row>Remove</button>' +
            '</div>';
    });
    html += '</div><button type="button" class="mlf-small-btn" data-mlf-add-link>Add Link</button>';
    return html;
}

function mlfRenderEditField(key, field, value) {
    var type = mlfFieldType(field);
    var label = field.label || key.replace(/-/g, ' ').replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
    var description = field.description ? '<p class="mlf-field-description">' + mlfEscapeHtml(field.description) + '</p>' : '';
    var required = field.required ? ' required' : '';
    var placeholder = field.placeholder ? ' placeholder="' + mlfEscapeHtml(field.placeholder) + '"' : '';
    var options = mlfOptionsToArray(field.options);
    var values = mlfNormalizeListValue(value);
    var html = '<div class="mlf-edit-field mlf-field-type-' + mlfEscapeHtml(type) + '"><label>' + mlfEscapeHtml(label) + '</label>' + description;

    if (key === 'job_title') {
        return html + '<input type="text" name="job_title" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input"' + required + placeholder + '></div>';
    }

    switch (type) {
        case 'email':
            html += '<input type="email" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input"' + required + placeholder + '>';
            break;
        case 'url':
        case 'link':
            html += '<input type="url" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input"' + required + placeholder + '>';
            break;
        case 'number':
            html += '<input type="number" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input"' + required + placeholder + '>';
            break;
        case 'textarea':
            html += '<textarea name="' + mlfEscapeHtml(key) + '" rows="5" class="mlf-edit-input"' + required + placeholder + '>' + mlfEscapeHtml(value) + '</textarea>';
            break;
        case 'select':
            html += '<select name="' + mlfEscapeHtml(key) + '" class="mlf-edit-input"' + required + '><option value=""></option>';
            options.forEach(function(option) {
                var selected = String(value) === String(option.value) ? ' selected' : '';
                html += '<option value="' + mlfEscapeHtml(option.value) + '"' + selected + '>' + mlfEscapeHtml(option.label) + '</option>';
            });
            html += '</select>';
            break;
        case 'multiselect':
            html += '<input type="hidden" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(values.join(', ')) + '" data-mlf-multi-value>';
            html += '<select multiple class="mlf-edit-input" data-mlf-multi-select' + required + '>';
            options.forEach(function(option) {
                var selected = mlfOptionSelected(option, values) ? ' selected' : '';
                html += '<option value="' + mlfEscapeHtml(option.value) + '"' + selected + '>' + mlfEscapeHtml(option.label) + '</option>';
            });
            if (!options.length && values.length) {
                values.forEach(function(item) {
                    html += '<option value="' + mlfEscapeHtml(item) + '" selected>' + mlfEscapeHtml(item) + '</option>';
                });
            }
            html += '</select>';
            break;
        case 'checkbox':
            html += '<input type="hidden" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(values.join(', ')) + '" data-mlf-check-value>';
            html += '<div class="mlf-choice-group">';
            if (!options.length && values.length) {
                options = values.map(function(item) { return { value: item, label: item }; });
            }
            options.forEach(function(option) {
                var checked = mlfOptionSelected(option, values) ? ' checked' : '';
                html += '<label class="mlf-choice"><input type="checkbox" value="' + mlfEscapeHtml(option.value) + '"' + checked + ' data-mlf-check-option> ' + mlfEscapeHtml(option.label) + '</label>';
            });
            html += '</div>';
            break;
        case 'radio':
        case 'boolean':
            html += '<div class="mlf-choice-group">';
            (options.length ? options : [{value: 'Yes', label: 'Yes'}, {value: 'No', label: 'No'}]).forEach(function(option) {
                var checked = String(value) === String(option.value) || String(value) === String(option.label) ? ' checked' : '';
                html += '<label class="mlf-choice"><input type="radio" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(option.value) + '"' + checked + '> ' + mlfEscapeHtml(option.label) + '</label>';
            });
            html += '</div>';
            break;
        case 'file':
        case 'image':
            html += mlfRenderMediaPreview(key, value, field.multiple || key === 'job_gallery');
            break;
        case 'gallery':
            html += mlfRenderMediaPreview(key, value, true);
            break;
        case 'links':
            html += mlfRenderLinksField(key, value);
            break;
        case 'location':
        case 'map':
            html += '<input type="text" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input mlf-location-input"' + required + placeholder + '>';
            break;
        case 'work-hours':
            html += mlfRenderWorkHours(key, value);
            break;
        default:
            html += '<input type="text" name="' + mlfEscapeHtml(key) + '" value="' + mlfEscapeHtml(value) + '" class="mlf-edit-input"' + required + placeholder + '>';
            break;
    }

    return html + '</div>';
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
                    var data = response.data;
                    var fields = data.fields || {};
                    var meta = data.edit_meta || data.meta || {};

                    var html = '<div class="mlf-back-btn" onclick="mlfOpenDetail(' + id + ')">&#8592; Back to details</div>';
                    html += '<form id="mlf-edit-form">';
                    html += '<input type="hidden" name="id" value="' + id + '">';

                    Object.keys(fields).sort(function(a, b) {
                        return (parseInt(fields[a].priority || 0, 10) - parseInt(fields[b].priority || 0, 10));
                    }).forEach(function(key) {
                        var field = fields[key] || {};
                        if (field.type === 'form-heading') {
                            html += '<div class="mlf-edit-section-heading"><h4>' + mlfEscapeHtml(field.label || key) + '</h4></div>';
                            return;
                        }

                        var value = key === 'job_title' ? data.title : (meta[key] || '');
                        html += mlfRenderEditField(key, field, value);
                    });

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
    function mlfSyncStructuredEditFields(form) {
        if (!form) return;

        $(form).find('[data-mlf-multi-value]').each(function() {
            var selected = $(this).siblings('[data-mlf-multi-select]').val() || [];
            $(this).val(selected.join(', '));
        });

        $(form).find('[data-mlf-check-value]').each(function() {
            var checked = [];
            $(this).siblings('.mlf-choice-group').find('[data-mlf-check-option]:checked').each(function() {
                checked.push($(this).val());
            });
            $(this).val(checked.join(', '));
        });

        $(form).find('[data-mlf-links-value]').each(function() {
            var items = [];
            $(this).siblings('[data-mlf-links-list]').find('.mlf-repeatable-row').each(function() {
                var network = $.trim($(this).find('[data-mlf-link-network]').val() || '');
                var url = $.trim($(this).find('[data-mlf-link-url]').val() || '');
                if (network || url) {
                    items.push(network ? network + ':' + url : url);
                }
            });
            $(this).val(items.join(', '));
        });

        $(form).find('[data-mlf-work-hours-value]').each(function() {
            var wrapper = $(this).siblings('.mlf-work-hours-editor');
            var value = {
                timezone: $.trim(wrapper.find('[data-mlf-work-hours-timezone]').val() || '')
            };
            
            wrapper.find('[data-mlf-work-hours-day]').each(function() {
                var row = $(this);
                var day = row.data('mlf-work-hours-day');
                var status = row.find('[data-mlf-work-hours-status]').val() || 'closed';
                var from = row.find('[data-mlf-work-hours-from]').val() || '';
                var to = row.find('[data-mlf-work-hours-to]').val() || '';
                
                value[day] = {
                    status: status,
                    hours: status === 'enter-hours' && (from || to) ? [{ from: from, to: to }] : []
                };
            });
            
            $(this).val(JSON.stringify(value));
        });
    }

    $(document).on('change', '#mlf-edit-form [data-mlf-multi-select], #mlf-edit-form [data-mlf-check-option], #mlf-edit-form [data-mlf-work-hours-status], #mlf-edit-form [data-mlf-work-hours-from], #mlf-edit-form [data-mlf-work-hours-to]', function() {
        mlfSyncStructuredEditFields(document.getElementById('mlf-edit-form'));
    });

    $(document).on('input', '#mlf-edit-form [data-mlf-link-network], #mlf-edit-form [data-mlf-link-url], #mlf-edit-form [data-mlf-work-hours-timezone]', function() {
        mlfSyncStructuredEditFields(document.getElementById('mlf-edit-form'));
    });

    $(document).on('click', '#mlf-edit-form [data-mlf-add-link]', function() {
        var list = $(this).siblings('[data-mlf-links-list]');
        list.append('<div class="mlf-repeatable-row"><input type="text" value="" placeholder="Network" data-mlf-link-network class="mlf-edit-input"><input type="url" value="" placeholder="URL" data-mlf-link-url class="mlf-edit-input"><button type="button" class="mlf-small-btn" data-mlf-remove-row>Remove</button></div>');
    });

    $(document).on('click', '#mlf-edit-form [data-mlf-remove-row]', function() {
        var form = document.getElementById('mlf-edit-form');
        $(this).closest('.mlf-repeatable-row').remove();
        mlfSyncStructuredEditFields(form);
    });

    window.mlfSaveEdit = function(id) {
        var form     = document.getElementById('mlf-edit-form');
        mlfSyncStructuredEditFields(form);
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
