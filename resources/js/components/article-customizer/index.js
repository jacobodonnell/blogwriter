import { createTiptapEditor } from '../../extensions/tiptap-editor';
import { makeDirtyTracker } from './dirty-tracker';
import { makePublishState } from './publish-state';
import { makeTiptapCommands } from './tiptap-commands';
import { makeSave } from './save';

function mergeDescriptors(target, ...sources) {
    for (const source of sources) {
        Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
    }
    return target;
}

export default function articleCustomizer(config) {
    let editor = null;
    const getEditor = () => editor;

    return mergeDescriptors(
        {
            title: config.title,
            slug: config.slug,
            content: config.content,
            summary: config.summary,
            categoryId: String(config.categoryId ?? ''),
            metaTitle: config.metaTitle ?? '',
            metaDescription: config.metaDescription ?? '',
            ogImage: config.ogImage ?? '',
            selectedPhotoId: config.selectedPhotoId,
            uploadedPhotoUrl: null,
            uploading: false,
            featuredImageUrl: config.featuredImageUrl,
            showUrlField: config.showUrlField,
            featuredImageCaption: config.featuredImageCaption,
            usePhotoCaption: config.usePhotoCaption,
            editorHeight: (() => {
                try { return JSON.parse(localStorage.getItem('editorHeight')) || '32rem'; }
                catch { return '32rem'; }
            })(),
            initialStatus: config.initialStatus,
            currentStatus: config.currentStatus,
            wasEverPublished: config.wasEverPublished,
            originalPublishedAt: config.originalPublishedAt,
            contentError: false,
            editorReady: false,
            markdownMode: false,
            hasNewPhoto: false,
            hasNewCategory: false,
            newCategoryName: '',
            updatedAt: 0,
            isNew: config.isNew ?? false,

            get isPlaceholderSlug() {
                return /^untitled-[a-z0-9]{8}$/.test(this.slug);
            },

            get displaySlug() {
                return this.isPlaceholderSlug ? '' : this.slug;
            },
            set displaySlug(v) {
                this.slug = v;
            },

            get wordCount() {
                void this.updatedAt;
                return editor?.wordCount ?? 0;
            },

            generateSlug() {
                if (this.title && (!this.slug || this.slug.match(/^untitled-[a-z0-9]{8}$/))) {
                    this.slug = this.title.toLowerCase()
                        .replace(/[''`\u2018\u2019\u2032\u02BC]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            },

            handlePhotoAttached({ file, alt, caption }) {
                if (!file || !alt.trim()) return;

                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.featuredImageFileInput.files = dt.files;
                this.$refs.featuredImageAltInput.value = alt.trim();
                this.$refs.featuredImageCaptionInput.value = caption;

                this.uploadedPhotoUrl = URL.createObjectURL(file);
                this.selectedPhotoId = '';
                this.featuredImageUrl = '';
                this.showUrlField = false;
                this.hasNewPhoto = true;
            },

            toggleMarkdownMode() {
                if (!this.markdownMode) {
                    this.content = editor.getMarkdown();
                } else {
                    editor.setContent(this.content);
                }
                this.markdownMode = !this.markdownMode;
                this.checkDirty();
            },

            init() {
                this.$watch('editorHeight', v => {
                    try { localStorage.setItem('editorHeight', JSON.stringify(v)); } catch {}
                });

                this.$nextTick(() => {
                    const el = this.$refs.contentEditor;
                    if (!el) return;

                    editor = createTiptapEditor({
                        element: el,
                        content: this.content,
                        onUpdate: (e) => {
                            this.updatedAt = Date.now();
                            this.content = e.getMarkdown();
                            this.checkDirty();
                            this.contentError = false;
                            this.$refs.customizerForm.dispatchEvent(
                                new Event('input', { bubbles: true })
                            );
                        },
                        onSelectionUpdate: () => {
                            this.updatedAt = Date.now();
                        },
                    });

                    this.editorReady = true;
                    const normalised = editor.getMarkdown();
                    this.content = normalised;
                    if (!config.hasDraft) {
                        this.savedContent = normalised;
                    }
                    this.updatedAt = Date.now();

                    const ro = new ResizeObserver(entries => {
                        for (const entry of entries) {
                            if (entry.target.style.height) {
                                this.editorHeight = entry.target.style.height;
                            }
                        }
                    });
                    ro.observe(el);
                });

                const store = Alpine.store('saveButton');
                store.label = this.buttonLabel;
                store.icon = this.buttonIcon;
                store.cssClass = this.buttonClass;
                store.action = this.buttonAction;
                store.ready = true;
                store.hasDraft = this.hasDraft;

                this.$watch('buttonLabel', v => store.label = v);
                this.$watch('buttonIcon', v => store.icon = v);
                this.$watch('buttonClass', v => store.cssClass = v);
                this.$watch('buttonAction', v => store.action = v);
                this.$watch('hasDraft', v => store.hasDraft = v);

                this._onSaveArticle = () => {
                    if (this.buttonAction === 'publish') { this.$refs.publishModal.showModal(); return; }
                    if (this.buttonAction === 'republish') { this.$refs.republishModal.showModal(); return; }
                    if (this.buttonAction === 'unpublish') { this.$refs.unpublishModal.showModal(); return; }
                    this.submitFullSave();
                };

                this._onKeydown = (e) => {
                    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                        e.preventDefault();
                        window.dispatchEvent(new CustomEvent('save-article'));
                    }
                };

                this._onBeforeUnload = (e) => {
                    if (this.isNew && this.hasChanges) { e.preventDefault(); }
                };

                window.addEventListener('save-article', this._onSaveArticle);
                window.addEventListener('keydown', this._onKeydown);
                window.addEventListener('beforeunload', this._onBeforeUnload);
            },

            destroy() {
                window.removeEventListener('save-article', this._onSaveArticle);
                window.removeEventListener('keydown', this._onKeydown);
                window.removeEventListener('beforeunload', this._onBeforeUnload);
                editor?.destroy();
                editor = null;
            },
        },
        makeDirtyTracker(config, getEditor),
        makePublishState(),
        makeTiptapCommands(getEditor),
        makeSave(config, getEditor),
    );
}
