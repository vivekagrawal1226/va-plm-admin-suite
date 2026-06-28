/**
 * VA PLM Admin Suite - Core UX Orchestration & Split BOM Attribute Engineering Engine
 * Centralized Client-Side Event Controller for /wp-admin Lifecycles
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/js
 */

(function($) {
    'use strict';

    $(function() {
        // Cache core localization and routing data
        //var l10n = typeof window.wpVaplmAdminL10n !== 'undefined' ? window.wpVaplmAdminL10n : {};
		var l10n = typeof window.vaplmAdminSuiteL10n !== 'undefined' ? window.vaplmAdminSuiteL10n : {};
        var ajaxUrl = l10n.ajaxUrl || ajaxurl;

        // --------------------------------------------------------------------------
        // 1. CORE FORM METRICS, UI LOCKS & TABBED NAVIGATION
        // --------------------------------------------------------------------------

        var $toggleEditBtn     = $('#vaplm-custom-toggle-edit-btn');
        var $mutableFields     = $('.vaplm-tabbed-app-container input, .vaplm-tabbed-app-container select, .vaplm-tabbed-app-container textarea').not('.vaplm-readonly-token-field');
        var $editableOnlyCells = $('.vaplm-editable-only-element, .vaplm-editable-only-element-cell');
        
        // Re-enable all fields immediately before form submission so the payload serializes correctly
        $('#post').on('submit', function() {
            $('.vaplm-tabbed-app-container input, .vaplm-tabbed-app-container select, .vaplm-tabbed-app-container textarea').prop('disabled', false);
        });

        // Toggle Edit Mode
        if ($toggleEditBtn.length > 0) {
            $mutableFields.prop('disabled', true);
            $editableOnlyCells.hide();

            $toggleEditBtn.on('click', function(e) {
                e.preventDefault();
                $mutableFields.prop('disabled', false);
                $editableOnlyCells.fadeIn(200);
                $(this).text('📝 ' + (l10n.editModeActive || 'Edit Mode Active'))
                       .removeClass('button-primary').addClass('button-secondary')
                       .css({'background-color': '#edfaef', 'color': '#257e3a', 'border-color': '#ade7b9'});
                
                if (!$('#vaplm-edit-mode-notice-banner').length) {
                    $('.vaplm-workspace-toolbar-accelerator').after('<div id="vaplm-edit-mode-notice-banner" class="notice notice-warning inline"><p>' + (l10n.editModeWarning || 'You are modifying a live engineering record context.') + '</p></div>');
                }
            });
        }

        // Horizontal Tab Switching (Post Editor & Workspace)
        $(document).on('click', '.vaplm-nav-tab-wrapper a', function(e) {
            var $anchor = $(this);
            var targetPanelId = $anchor.attr('href');

            // Only intercept the click if the link is an on-page hash target
            if (!targetPanelId || targetPanelId.indexOf('#') !== 0) {
                return; // Let the browser follow the URL normally
            }

            e.preventDefault();
            $anchor.addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
            $(targetPanelId).addClass('vaplm-tab-panel-active').siblings('.vaplm-tab-panel').removeClass('vaplm-tab-panel-active');
        });

        // --------------------------------------------------------------------------
        // 2. ATTACHMENTS VAULT (WP MEDIA LIBRARY INTEGRATION)
        // --------------------------------------------------------------------------
        
        var wpMediaFrame;
        $('#vaplm-add-multi-binary-btn').on('click', function(e) {
            e.preventDefault();

            if (wpMediaFrame) {
                wpMediaFrame.open();
                return;
            }

            wpMediaFrame = wp.media({
                title: l10n.uploadTitle || 'Select Engineering Documentation',
                button: { text: l10n.uploadBtnText || 'Bind Binaries' },
                multiple: true
            });

            wpMediaFrame.on('select', function() {
                var selection = wpMediaFrame.state().get('selection');
                var $tbody = $('#vaplm-attachments-grid-table tbody');
                
                // Drop empty placeholder if exists
                $tbody.find('.vaplm-empty-attachments-row').remove();

                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    var existing = $tbody.find('tr[data-attachment-id="' + attachment.id + '"]');
                    if (existing.length === 0) {
                        var mimeDisplay = attachment.mime ? attachment.mime.replace('application/', '').toUpperCase() : (l10n.unknownFormat || 'UNKNOWN');
                        var fileName = attachment.filename || attachment.title;
                        
                        var newRow = '<tr data-attachment-id="' + attachment.id + '">' +
                            '<td><a href="' + attachment.url + '" target="_blank" class="vaplm-file-link-accent">📁 ' + fileName + '</a></td>' +
                            '<td><code>' + mimeDisplay + '</code></td>' +
                            '<td style="text-align: center;" class="vaplm-editable-only-element-cell">' +
                            '<button type="button" class="button vaplm-remove-attachment-row-btn" style="color: #d63638;">✕ Detach</button>' +
                            '<input type="hidden" name="vaplm_attachments[]" value="' + attachment.id + '" />' +
                            '</td></tr>';
                        $tbody.append(newRow);
                    }
                });
            });

            wpMediaFrame.open();
        });

        $(document).on('click', '.vaplm-remove-attachment-row-btn', function(e) {
            e.preventDefault();
            var $tbody = $(this).closest('tbody');
            $(this).closest('tr').remove();
            if ($tbody.find('tr').length === 0) {
                $tbody.append('<tr class="vaplm-empty-attachments-row"><td colspan="3" style="text-align: center; color: #646970;">' + (l10n.noAttachments || 'No attachments in vault.') + '</td></tr>');
            }
        });

        // --------------------------------------------------------------------------
        // 3. SCHEMA MANAGERS (PARTS, DOCS, BOM, CHANGE DYNAMIC FIELDS)
        // --------------------------------------------------------------------------

        // Unified Add Row Function for Schema Configs
        $('.vaplm-add-schema-row-btn').on('click', function(e) {
            e.preventDefault();
            var targetTableId = $(this).data('target');
            var objectType = $(this).data('object-type');
            var subScope = $(this).data('sub-scope');
            var transientId = 'new_' + Date.now();
            
            var $tbody = $('#' + targetTableId + ' tbody');
            $tbody.find('.vaplm-empty-schema-fallback-row').remove();

            var lovSelectHtml = '<option value="">-- None Selected --</option>';

            if (l10n.lovDictionaries && Array.isArray(l10n.lovDictionaries)) {
                l10n.lovDictionaries.forEach(function(slug) {
                    lovSelectHtml += '<option value="' + slug + '">' + slug + '</option>';
                });
            }

            var subtypeHtml = '';
            if (subScope) {
                // BUGFIX: Insert specific hidden sub_scope if directed by the split views.
                subtypeHtml = '<input type="hidden" name="vaplm_dynamic_fields['+transientId+'][object_subtype]" value="'+subScope+'" />';
            } else {
                subtypeHtml = '<select name="vaplm_dynamic_fields['+transientId+'][object_subtype]"><option value="">Global (All Sub-types)</option></select>';
            }

            var rowHtml = '<tr>' +
                '<td><input type="text" name="vaplm_dynamic_fields['+transientId+'][key]" placeholder="e.g., custom_field_id" required style="font-family:monospace;" /><input type="hidden" name="vaplm_dynamic_fields['+transientId+'][object_type]" value="'+objectType+'" />' + subtypeHtml + '</td>' +
                '<td><input type="text" name="vaplm_dynamic_fields['+transientId+'][label]" placeholder="Attribute Label" required /></td>' +
                '<td><select name="vaplm_dynamic_fields['+transientId+'][field_type]" class="vaplm-schema-attribute-ui-selector-trigger"><option value="text">Alphanumeric String</option><option value="number">Numeric Decimal</option><option value="lov_dropdown">LOV Dropdown</option></select></td>' +
                '<td><select name="vaplm_dynamic_fields['+transientId+'][lov_target]" class="vaplm-schema-attribute-lov-target-dropdown" disabled>' + lovSelectHtml + '</select></td>' +
                '<td style="text-align:center;"><button type="button" class="button vaplm-remove-schema-row-btn" style="color:#d63638;">✕</button></td>' +
                '</tr>';
                
            $tbody.append(rowHtml);
        });

        // Toggle LOV target requirement
        $(document).on('change', '.vaplm-schema-attribute-ui-selector-trigger', function() {
            var $lovDropdown = $(this).closest('tr').find('.vaplm-schema-attribute-lov-target-dropdown');
            if ($(this).val() === 'lov_dropdown') {
                $lovDropdown.prop('disabled', false).attr('required', true);
            } else {
                $lovDropdown.prop('disabled', true).val('').removeAttr('required');
            }
        });

        // Remove uncommitted schema row
        $(document).on('click', '.vaplm-remove-schema-row-btn', function(e) {
            e.preventDefault();
            var $tbody = $(this).closest('tbody');
            $(this).closest('tr').remove();
            if ($tbody.find('tr').length === 0) {
                $tbody.append('<tr class="vaplm-empty-schema-fallback-row"><td colspan="5" style="text-align:center; padding:15px; color:#646970;">No attributes currently mapped.</td></tr>');
            }
        });

        // --------------------------------------------------------------------------
        // 4. BILL OF MATERIALS (BOM) ENGINEERING MATRIX
        // --------------------------------------------------------------------------
        
        var $metaStore = $('#vaplm-meta-box-data-store');
        if ($metaStore.length && $('#vaplm-bom-assembly-lines-matrix-table').length) {
            
            var bomColumnsSchema = [];
            var partsInventory = {};
            
            try {
                bomColumnsSchema = JSON.parse($metaStore.attr('data-bom-columns') || '{}');
                partsInventory = JSON.parse($metaStore.attr('data-parts-inventory') || '{}');
            } catch(e) { console.error('VA PLM JSON Parse Error:', e); }

            $('#vaplm-bom-add-component-line-item-trigger-btn').on('click', function(e) {
                e.preventDefault();
                var $tbody = $('#vaplm-bom-assembly-rows-injection-body');
                var timestampId = Date.now();
                
                var partsOptions = '<option value="">-- Select Child Part --</option>';
                $.each(partsInventory, function(id, data) {
                    partsOptions += '<option value="' + id + '">' + data.number + ' - ' + data.title + '</option>';
                });

                var uomOptionsHtml = '';
                if (l10n.lovDictionaries && l10n.lovDictionaries.indexOf('uom_codes') !== -1) {
                    uomOptionsHtml += '<option value="ea" selected>EA</option>'; // Simplified fallback for JS injection
                }

                var dynamicCellsHtml = '';
                $.each(bomColumnsSchema, function(colKey, colConfig) {
                    dynamicCellsHtml += '<td>';
                    if (colConfig.type === 'lov_dropdown' && colConfig.options) {
                        dynamicCellsHtml += '<select name="vaplm_bom_components[' + timestampId + '][' + colKey + ']" style="width: 100%; height: 32px;"><option value="">-- Select --</option>';
                        $.each(colConfig.options, function(idx, opt) {
                            dynamicCellsHtml += '<option value="' + opt.value + '">' + opt.label + '</option>';
                        });
                        dynamicCellsHtml += '</select>';
                    } else {
                        dynamicCellsHtml += '<input type="text" name="vaplm_bom_components[' + timestampId + '][' + colKey + ']" value="' + (colConfig.default || '') + '" style="width: 100%; height: 32px;" placeholder="' + colConfig.label + '..." />';
                    }
                    dynamicCellsHtml += '</td>';
                });

                var lineMarkup = '<tr data-row-index="' + timestampId + '">' +
                    '<td style="vertical-align: middle;"><div style="display: flex; align-items: center; gap: 8px;">' +
                    '<select name="vaplm_bom_components[' + timestampId + '][child_id]" class="vaplm-bom-child-part-selector-dropdown" style="flex-grow: 1; height: 32px;" required>' + partsOptions + '</select>' +
                    '</div></td>' +
                    '<td><input type="number" name="vaplm_bom_components[' + timestampId + '][quantity]" value="1.0000" min="0.0001" step="0.0001" style="width: 100%; height: 32px;" required /></td>' +
                    '<td><select name="vaplm_bom_components[' + timestampId + '][uom]" style="width: 100%; height: 32px;" required>' + uomOptionsHtml + '</select></td>' +
                    dynamicCellsHtml +
                    '<td style="text-align: center;"><button type="button" class="button vaplm-bom-delete-component-row-action-btn" style="color: #d63638; border-color: #ccd0d4; height: 32px; line-height: 30px;">✕ Drop</button></td>' +
                    '</tr>';

                $tbody.append(lineMarkup);
            });

            $(document).on('click', '.vaplm-bom-delete-component-row-action-btn', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        }

        // --------------------------------------------------------------------------
        // 5. ANALYTICAL WORKSPACE (ADVANCED QUERIES)
        // --------------------------------------------------------------------------

        if ($('#vaplm-workspace-dashboard-advanced-query-builder').length) {
            
            // Helper function to safely duplicate the dropdown so custom fields aren't lost
            function createRuleRowHtml() {
                var $existingSelect = $('.vaplm-rule-field').first();
                var selectHtml = '';
                if ($existingSelect.length) {
                    selectHtml = $existingSelect[0].outerHTML;
                } else {
                    selectHtml = '<select class="vaplm-rule-field" style="width: 100%; height: 32px;"><option value="post_title">Object Title</option><option value="vaplm_lifecycle_status">Lifecycle Status</option><option value="vaplm_object_number">Object Number</option></select>';
                }

                return '<div class="vaplm-query-rule-row" style="display:flex; gap:10px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px dashed #f0f0f1; align-items:center;">' +
                    '<div style="width: 30%;">' + selectHtml + '</div>' +
                    '<div style="width: 25%;">' +
                    '<select class="vaplm-rule-op" style="width: 100%; height: 32px;">' +
                    '<option value="equals">== Equals</option>' +
                    '<option value="not_equals">!= Not Equals</option>' +
                    '<option value="contains">⊂ Contains</option>' +
                    '<option value="is_blank">∅ Is Blank</option>' +
                    '</select></div>' +
                    '<div style="width: 40%;"><input type="text" class="vaplm-rule-val" placeholder="Enter validation search constraint string..." style="width: 100%; height: 32px; box-sizing: border-box;" /></div>' +
                    '<div style="width: 5%; text-align: center;"><button type="button" class="button vaplm-remove-rule-btn" style="color: #d63638; border-color: #ccd0d4; padding: 0 8px; height: 32px; line-height: 30px;">✕</button></div>' +
                    '</div>';
            }
            
            $('#vaplm-add-query-rule-btn').on('click', function() {
                $('#vaplm-query-rules-container').append(createRuleRowHtml());
            });

            $(document).on('click', '.vaplm-remove-rule-btn', function() {
                $(this).closest('.vaplm-query-rule-row').remove();
            });

            // BUGFIX: Execute Saved Reports Functionality
            $(document).on('click', '.vaplm-trigger-saved-report-btn', function(e) {
                e.preventDefault();
                var rules = $(this).data('rules');
                var cpt = $(this).data('cpt');

                if (typeof rules === 'string') {
                    try { rules = JSON.parse(rules); } catch(err) { rules = []; }
                }

                $('#vaplm-query-target-object').val(cpt);
                $('#vaplm-query-rules-container').empty();

                if (rules && rules.length > 0) {
                    rules.forEach(function(rule) {
                        var $row = $(createRuleRowHtml());
                        $row.find('.vaplm-rule-field').val(rule.field);
                        $row.find('.vaplm-rule-op').val(rule.operator);
                        $row.find('.vaplm-rule-val').val(rule.value);
                        $('#vaplm-query-rules-container').append($row);
                    });
                } else {
                    $('#vaplm-query-rules-container').append(createRuleRowHtml());
                }

                // Fire the AJAX execution trigger immediately
                $('#vaplm-run-advanced-query-btn').trigger('click');
            });

            $('#vaplm-run-advanced-query-btn').on('click', function() {
                var objType = $('#vaplm-query-target-object').val();
                var rules = [];
                var nonce = $('#vaplm_workspace_security_nonce').val();
                
                $('.vaplm-query-rule-row').each(function() {
                    rules.push({
                        field: $(this).find('.vaplm-rule-field').val(),
                        operator: $(this).find('.vaplm-rule-op').val(),
                        value: $(this).find('.vaplm-rule-val').val()
                    });
                });

                var $resultsBody = $('#vaplm-query-results-table tbody');
                $resultsBody.html('<tr><td colspan="5" style="text-align:center;">Executing Matrix Query...</td></tr>');

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'vaplm_execute_advanced_matrix_query',
                        nonce: nonce,
                        object_type: objType,
                        rules: rules
                    },
                    success: function(response) {
                        $resultsBody.empty();
                        if (response.success && response.data && response.data.length > 0) {
                            response.data.forEach(function(item) {
                                $resultsBody.append('<tr>' +
                                    '<td><strong><a href="post.php?post=' + item.id + '&action=edit">' + item.object_number + '</a></strong></td>' +
                                    '<td>' + item.post_title + '</td>' +
                                    '<td><span class="vaplm-item-type-pill">' + item.status + '</span></td>' +
                                    '<td>' + item.modified_date + '</td>' +
                                    '<td><a href="post.php?post=' + item.id + '&action=edit" class="button button-small">View/Edit</a></td>' +
                                    '</tr>');
                            });
                        } else {
                            $resultsBody.html('<tr><td colspan="5" style="text-align:center; color:#646970;">No objects matched the query criteria.</td></tr>');
                        }
                    },
                    error: function() {
                        $resultsBody.html('<tr><td colspan="5" style="text-align:center; color:#d63638;">' + (l10n.queryExecutionErr || 'Query Execution Failed.') + '</td></tr>');
                    }
                });
            });

            $('#vaplm-save-query-config-btn').on('click', function() {
                var reportName = prompt('Enter a name for this Analytical Report Configuration:');
                if (!reportName) return;

                var objType = $('#vaplm-query-target-object').val();
                var rules = [];
                var nonce = $('#vaplm_workspace_security_nonce').val();

                $('.vaplm-query-rule-row').each(function() {
                    rules.push({
                        field: $(this).find('.vaplm-rule-field').val(),
                        operator: $(this).find('.vaplm-rule-op').val(),
                        value: $(this).find('.vaplm-rule-val').val()
                    });
                });

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'vaplm_save_engineering_report',
                        nonce: nonce,
                        report_name: reportName,
                        object_type: objType,
                        rules: rules
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(l10n.reportSaved || 'Report saved successfully.');
                            location.reload(); 
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });

            // Purge Report
            $('.vaplm-purge-saved-report-action-trigger-btn').on('click', function(e) {
                e.stopPropagation();
                if (!confirm('Permanently delete this report configuration?')) return;
                
                var reportId = $(this).data('report-id');
                var nonce = $('#vaplm_workspace_security_nonce').val();

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'vaplm_purge_engineering_report',
                        nonce: nonce,
                        report_id: reportId
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });
        }

        // --------------------------------------------------------------------------
        // 6. MISC UX FIXES & LIST BROWSER CLICKS
        // --------------------------------------------------------------------------

        // Object Browser Full-Row Click Navigation
        $('.vaplm-data-table tbody tr').on('click', function(e) {
            // Ignore if clicked on button, link, or input
            if ($(e.target).is('a, button, input, select, code, .row-actions *')) return; 
            
            var targetUrl = $(this).find('a.row-title').attr('href');
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        });

        // Trigger CSV Upload on LOV Tab Automatically
        $('#vaplm_lov_csv_file').on('change', function() {
            var $form = $(this).closest('form');
            if ($(this).val() !== '') {
                $('#vaplm_action_type').val('import_lov_csv');
                $form.submit();
            }
        });

    });

})(jQuery);