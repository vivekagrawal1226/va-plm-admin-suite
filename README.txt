=== VA PLM Admin Suite ===
Contributors: Vivek Agrawal
Tags: plm, product lifecycle management, bom, manufacturing, engineering, eco
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.5.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An enterprise-grade Product Lifecycle Management workspace built natively inside the /wp-admin dashboard context. Manage BOMs, ECOs, and RBAC securely.

== Description ==

The **VA PLM Admin Suite** is an enterprise-grade Product Lifecycle Management (PLM) solution that transforms your standard WordPress backend into a secure, high-density engineering workspace. Decoupled from public-facing themes to protect your intellectual property, this powerful tool equips engineering and manufacturing teams with everything they need to seamlessly orchestrate complex product data.

With built-in cross-object traceability, customizable lifecycle milestones, and bulk CSV importing capabilities, you can manage Parts, Documents, Bill of Materials (BOM), and Engineering Change Orders (ECO) natively within the `/wp-admin` dashboard.

= Core Features =

* **Engineering Object Management:** Natively manage Parts, Documents Vaults, BOM Assemblies, and Engineering Change Orders (ECO).
* **Dynamic Custom Attributes:** Provision custom metadata fields (Text, Numeric, LOV Dropdowns) and scope them globally or to specific classification sub-types.
* **Advanced BOM Architectures:** Build N-Tier Engineering Bill of Materials (EBOM) structures with dedicated quantity tracking, Unit of Measure (UOM) configurations, and custom BOM-specific grid columns.
* **List of Values (LOV) Dictionaries:** Maintain standardized dropdown selections across your engineering ecosystem. Easily append rows manually or via bulk CSV upload.
* **Granular RBAC Permissions Matrix:** Explicitly configure Create, Read, Update, and Upload capabilities for every user role (e.g., Manager, Engineer, Guest) in a visual grid.
* **Custom Lifecycle Milestones:** Define custom state milestones (e.g., *Prototype*, *SupplierHold*) alongside unalterable system gates (*Draft*, *In Review*, *Released*, *Obsolete*).
* **Autonumbering Masks:** Create dynamic naming conventions (e.g., `PRT-{YYYY}-{SEQ:6}`) for automatic, sequential item generation.
* **Analytical Query & Reporting Engine:** Execute multi-attribute matrix tracing lookups, save complex report configurations, and extract customizable CSV datasets.
* **Mass Data Injector:** Perform bulk processing and injection of engineering objects using standard CSV templates.
* **Compliance & Forensics:** Immutable tracking of creation dates, modification dates, authoring users, and unique Object IDs.

== Installation ==

1. Download the plugin archive and extract the `va-plm-admin-suite` folder.
2. Upload the `va-plm-admin-suite` folder to your `/wp-content/plugins/` directory.
3. Log in to your WordPress administrative dashboard.
4. Navigate to **Plugins > Installed Plugins**.
5. Locate **VA PLM Admin Suite** and click **Activate**.
6. Upon activation, the plugin will automatically generate the required custom MySQL tables (`vaplm_ebom`, `vaplm_relationships`, `vaplm_lov_entries`), register the engineering post types, and map default user roles.
7. Navigate to the new **VA PLM Workspace** menu in your sidebar to begin configuring your schemas and dictionaries.

== Frequently Asked Questions ==

= Will my engineering data be visible on the public frontend of my website? =

No. The VA PLM Admin Suite explicitly decouples its Custom Post Types from public loops and REST endpoints. Your intellectual property and engineering data remain strictly confined to the protected `/wp-admin` dashboard.

= Can I add my own custom fields to Parts and BOMs? =

Yes! The Configuration Control dashboard allows you to define infinite custom attributes (Text, Numeric, Dropdowns). You can apply these fields globally to an entire object class or restrict them to specific sub-type classifications.

= Does this plugin support importing legacy data? =

Yes. The plugin includes a **Bulk Import** engine. You can download a canonical CSV template matching your exact custom schema, populate it, and upload it to batch-create thousands of records at once.

= How do I manage who can edit what? =

The plugin features a built-in Role-Based Access Control (RBAC) matrix. You can define custom roles (e.g., "QA Inspector") and explicitly check boxes granting them permission to Create, Read, Update, or Upload engineering documents.

== Screenshots ==

1. **Workspace Analytics Hub:** The central dashboard featuring the multi-attribute matrix query builder and saved reports.
2. **Object Editor Canvas:** The high-density, multi-tab layout for modifying Parts, BOMs, and managing relationships.
3. **BOM Configuration:** The visual N-Tier BOM builder with dynamic column schemas and quantity allocations.
4. **Configuration Control:** The central settings hub to manage LOV dictionaries, autonumbering masks, and RBAC permissions.

== Changelog ==

= 1.5.4 =
* Fixed user check in the Admin class

= 1.5.3 =
* Security Hardening: Enforced strict CSRF nonce validation and capability checks (`edit_post`) on the `save_post` hook to prevent unauthorized taxonomy assignments.
* Architecture: Transitioned custom object editing canvas natively into the WordPress Classic Editor via standard Meta Boxes to resolve CSS conflicts and header streaming issues.
* Bugfix: Resolved visibility logic preventing global custom fields scoped to 'general' from rendering in the editor.
* Bugfix: Corrected database column mapping schemas (`parent_id`, `child_id`, `custom_data`) during BOM structure commits.
* Bugfix: Added `DOING_AUTOSAVE` verification to prevent empty data commits during background draft saves.

= 1.5.2 =
* Fix: Synced Stable Tag and Plugin header formatting to resolve repository mismatch flags.
* Code Quality: Injected missing translator syntax comments across all printf/sprintf localizations to comply with core standard.

= 1.5.1 =
* Rebranded to VA PLM Admin Suite.
* Security Hardening: Comprehensive sanitization implemented across all input layers using strict `sanitize_text_field( wp_unslash() )` processing.
* Security Hardening: 100% Nonce and Permission coverage applied to all AJAX endpoints and form submissions.
* Performance: Extracted all inline JavaScript and CSS into dedicated, localized assets (`vaplm-admin.js`, `vaplm-admin.css`).
* Architecture: Applied strict `vaplm_` prefixing to all functions, classes, globals, and database schemas to prevent third-party collisions.
* Bugfix: Resolved issue where saving BOM General Properties would overwrite BOM Matrix Column configurations.
* Bugfix: Fixed routing issue where horizontal tabs in the Configuration Control dashboard intercepted valid URL clicks.
* Bugfix: Reconnected the Saved Reports AJAX execution triggers in the Analytics Hub.

= 1.5.0 =
* Initial stable release of the enterprise framework.