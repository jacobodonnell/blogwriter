import { Editor, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
import { Markdown } from '@tiptap/markdown';

export default function articleCustomizer(config) {
    let rawEditor = null;

    return {
        title: config.title,
        slug: config.slug,
        content: config.content,
        summary: config.summary,
        selectedPhotoId: config.selectedPhotoId,
        uploadedPhotoUrl: null,
        uploading: false,
        featuredImageUrl: config.featuredImageUrl,
        showUrlField: config.showUrlField,
        featuredImageCaption: config.featuredImageCaption,
        usePhotoCaption: config.usePhotoCaption,
        showLinkDialog: false,
        linkUrl: '',
        showImageDialog: false,
        editingImage: false,
        imageUrl: '',
        imageAlt: '',
        imageCaption: '',
        imageAlign: 'center',
        showYoutubeDialog: false,
        youtubeUrl: '',
        initialStatus: config.initialStatus,
        currentStatus: config.currentStatus,
        wasEverPublished: config.wasEverPublished,
        originalPublishedAt: config.originalPublishedAt,
        contentError: false,
        editorReady: false,
        hasNewPhoto: false,
        updatedAt: 0,

        get isPlaceholderSlug() {
            return /^untitled-[a-z0-9]{8}$/.test(this.slug);
        },

        get displaySlug() {
            return this.isPlaceholderSlug ? '' : this.slug;
        },
        set displaySlug(v) {
            this.slug = v;
        },

        generateSlug() {
            if (this.title && (!this.slug || this.slug.match(/^untitled-[a-z0-9]{8}$/))) {
                this.slug = this.title.toLowerCase()
                    .replace(/[''`\u2018\u2019\u2032\u02BC]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        },

        get buttonAction() {
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && !this.wasEverPublished) return 'publish';
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && this.wasEverPublished) return 'republish';
            if (this.currentStatus === 'draft' && this.initialStatus === 'published') return 'unpublish';
            return 'save';
        },
        get buttonLabel() {
            const a = this.buttonAction;
            const p = this.hasNewPhoto;
            if (a === 'publish') return p ? 'Upload Photo & Publish' : 'Publish Article';
            if (a === 'republish') return p ? 'Upload Photo & Republish' : 'Republish Article';
            if (a === 'unpublish') return 'Unpublish Article';
            if (this.initialStatus === 'published') return p ? 'Upload Photo & Save Changes' : 'Save Changes';
            return p ? 'Upload Photo & Save Draft' : 'Save Draft';
        },
        get buttonIcon() {
            if (this.hasNewPhoto) return 'ph-upload-simple';
            if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'ph-rocket-launch';
            if (this.buttonAction === 'unpublish') return 'ph-arrow-u-up-left';
            return 'ph-floppy-disk';
        },
        get buttonClass() {
            if (this.hasNewPhoto) return 'btn-success';
            if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'btn-success';
            if (this.buttonAction === 'unpublish') return 'btn-error btn-outline';
            return 'btn-primary';
        },

        init() {
            this.$nextTick(() => {
                const el = document.getElementById('content-editor');
                if (!el) return;

                rawEditor = new Editor({
                    element: el,
                    extensions: [
                        StarterKit.configure({
                            heading: { levels: [2, 3, 4, 5] },
                            codeBlock: { languageClassPrefix: 'language-' },
                            link: { openOnClick: false },
                        }),
                        Image.configure({
                            resize: {
                                enabled: true,
                                directions: ['left', 'right'],
                                minWidth: 60,
                                alwaysPreserveAspectRatio: true,
                            },
                        }).extend({
                            addAttributes() {
                                return {
                                    ...this.parent?.(),
                                    align: { default: null },
                                    caption: { default: null },
                                };
                            },

                            renderMarkdown(node) {
                                const { src = '', alt = '', title, align, width, caption } = node.attrs ?? {};
                                const parts = [alt];
                                if (align) parts.push(`align:${align}`);
                                if (width) parts.push(`width:${width}`);
                                if (caption) parts.push(`caption:${encodeURIComponent(caption)}`);
                                const altStr = parts.join('|');
                                return title ? `![${altStr}](${src} "${title}")` : `![${altStr}](${src})`;
                            },

                            parseMarkdown(token, h) {
                                const parts = (token.text ?? '').split('|');
                                const alt = parts[0] ?? '';
                                const attrs = { src: token.href, alt, title: token.title ?? null };
                                for (const part of parts.slice(1)) {
                                    const colonIdx = part.indexOf(':');
                                    if (colonIdx === -1) continue;
                                    const key = part.slice(0, colonIdx).trim();
                                    const value = part.slice(colonIdx + 1).trim();
                                    if (key === 'align') attrs.align = value;
                                    if (key === 'width') attrs.width = parseInt(value, 10) || null;
                                    if (key === 'caption') attrs.caption = decodeURIComponent(value);
                                }
                                return h.createNode('image', attrs, []);
                            },

                            renderHTML({ HTMLAttributes }) {
                                const { align, caption, width, ...rest } = HTMLAttributes;
                                const cls = align ? `img-align-${align}` : null;
                                const imgAttrs = mergeAttributes(rest, {
                                    class: cls,
                                    style: width ? `width:${width}px` : null,
                                });
                                if (caption) {
                                    return ['figure', { class: cls }, ['img', imgAttrs], ['figcaption', {}, caption]];
                                }
                                return ['img', imgAttrs];
                            },
                        }),
                        Youtube.configure({ controls: true }).extend({
                            renderMarkdown: (node) => {
                                return `@[youtube](${node.attrs?.src || ''})`;
                            },
                            markdownTokenizer: {
                                name: 'youtube',
                                level: 'block',
                                start(src) {
                                    return src.search(/^@\[youtube\]/m);
                                },
                                tokenize(src) {
                                    const match = src.match(/^@\[youtube\]\(([^)]+)\)(?:\n|$)/);
                                    if (!match) return undefined;
                                    return { type: 'youtube', raw: match[0], attributes: { src: match[1] } };
                                },
                            },
                            parseMarkdown: (token, h) => {
                                return h.createNode('youtube', { src: token.attributes?.src }, []);
                            },
                        }),
                        Markdown.configure({ html: false, transformPastedText: true }),
                    ],
                    content: this.content || '',
                    contentType: 'markdown',
                    onUpdate: ({ editor }) => {
                        this.updatedAt = Date.now();
                        this.content = editor.getMarkdown();
                        this.contentError = false;
                        document.getElementById('customizer-form').dispatchEvent(
                            new Event('input', { bubbles: true })
                        );
                    },
                    onSelectionUpdate: () => {
                        this.updatedAt = Date.now();
                    },
                });

                this.editorReady = true;
            });

            const store = Alpine.store('saveButton');
            store.label = this.buttonLabel;
            store.icon = this.buttonIcon;
            store.cssClass = this.buttonClass;
            store.action = this.buttonAction;
            store.ready = true;

            this.$watch('buttonLabel', v => store.label = v);
            this.$watch('buttonIcon', v => store.icon = v);
            this.$watch('buttonClass', v => store.cssClass = v);
            this.$watch('buttonAction', v => store.action = v);

            window.addEventListener('save-article', () => {
                if (this.buttonAction === 'publish') { document.getElementById('publish-modal').showModal(); return; }
                if (this.buttonAction === 'republish') { document.getElementById('republish-modal').showModal(); return; }
                if (this.buttonAction === 'unpublish') { document.getElementById('unpublish-modal').showModal(); return; }
                this.submitFullSave();
            });
        },

        command(name) {
            if (!rawEditor) return;
            const map = {
                bold: () => rawEditor.chain().focus().toggleBold().run(),
                italic: () => rawEditor.chain().focus().toggleItalic().run(),
                h2: () => rawEditor.chain().focus().toggleHeading({ level: 2 }).run(),
                h3: () => rawEditor.chain().focus().toggleHeading({ level: 3 }).run(),
                h4: () => rawEditor.chain().focus().toggleHeading({ level: 4 }).run(),
                h5: () => rawEditor.chain().focus().toggleHeading({ level: 5 }).run(),
                blockquote: () => rawEditor.chain().focus().toggleBlockquote().run(),
                bulletList: () => rawEditor.chain().focus().toggleBulletList().run(),
                orderedList: () => rawEditor.chain().focus().toggleOrderedList().run(),
                code: () => rawEditor.chain().focus().toggleCode().run(),
                codeBlock: () => rawEditor.chain().focus().toggleCodeBlock().run(),
                horizontalRule: () => rawEditor.chain().focus().setHorizontalRule().run(),
                link: () => { this.linkUrl = ''; this.showLinkDialog = true; },
                image: () => {
                    if (rawEditor.isActive('image')) {
                        this.openEditImage();
                    } else {
                        this.resetImageDialog();
                        this.showImageDialog = true;
                    }
                },
                youtube: () => { this.youtubeUrl = ''; this.showYoutubeDialog = true; },
                imageAlignLeft:   () => rawEditor.chain().focus().updateAttributes('image', { align: 'left' }).run(),
                imageAlignCenter: () => rawEditor.chain().focus().updateAttributes('image', { align: 'center' }).run(),
                imageAlignRight:  () => rawEditor.chain().focus().updateAttributes('image', { align: 'right' }).run(),
                imageAlignFull:   () => rawEditor.chain().focus().updateAttributes('image', { align: 'full' }).run(),
                imageReset:       () => rawEditor.chain().focus().updateAttributes('image', { align: null, width: null }).run(),
            };
            map[name]?.();
        },

        isActive(name, attrs = {}) {
            // Reading updatedAt makes Alpine re-evaluate this whenever selection or content changes.
            void this.updatedAt;
            return rawEditor?.isActive(name, attrs) ?? false;
        },

        insertLink() {
            if (!this.linkUrl) return;
            rawEditor.chain().focus().setLink({ href: this.linkUrl }).run();
            this.showLinkDialog = false;
        },

        openEditImage() {
            if (!rawEditor || !rawEditor.isActive('image')) return;
            const attrs = rawEditor.getAttributes('image');
            this.imageUrl     = attrs.src     ?? '';
            this.imageAlt     = attrs.alt     ?? '';
            this.imageCaption = attrs.caption ?? '';
            this.imageAlign   = attrs.align   ?? 'center';
            this.editingImage = true;
            this.showImageDialog = true;
        },

        insertImage() {
            if (!this.imageUrl) return;
            const attrs = {
                src:     this.imageUrl,
                alt:     this.imageAlt     || undefined,
                align:   this.imageAlign   || undefined,
                caption: this.imageCaption || undefined,
            };
            if (this.editingImage) {
                rawEditor.chain().focus().updateAttributes('image', attrs).run();
            } else {
                rawEditor.chain().focus().setImage(attrs).run();
            }
            this.showImageDialog = false;
            this.editingImage = false;
            this.resetImageDialog();
        },

        isImageAlign(align) {
            void this.updatedAt;
            return rawEditor?.getAttributes('image')?.align === align;
        },

        resetImageDialog() {
            this.imageUrl     = '';
            this.imageAlt     = '';
            this.imageCaption = '';
            this.imageAlign   = 'center';
            this.editingImage = false;
        },

        insertYoutube() {
            if (!this.youtubeUrl) return;
            rawEditor.chain().focus().setYoutubeVideo({ src: this.youtubeUrl }).run();
            this.showYoutubeDialog = false;
        },

        destroy() {
            rawEditor?.destroy();
            rawEditor = null;
        },

        attachPhoto() {
            const picker = document.getElementById('photo-file-picker');
            const altInput = document.getElementById('photo-alt-text-input');
            if (!picker.files[0] || !altInput.value.trim()) return;

            const dt = new DataTransfer();
            dt.items.add(picker.files[0]);
            document.getElementById('featured-image-file-input').files = dt.files;
            document.getElementById('featured-image-alt-input').value = altInput.value.trim();
            document.getElementById('featured-image-caption-input').value = document.getElementById('photo-caption-input').value;

            this.uploadedPhotoUrl = URL.createObjectURL(picker.files[0]);
            this.selectedPhotoId = '';
            this.featuredImageUrl = '';
            this.showUrlField = false;
            this.hasNewPhoto = true;

            document.getElementById('upload-photo-modal').close();
        },

        submitFullSave() {
            if (rawEditor) {
                this.content = rawEditor.getMarkdown();
            }
            if (!this.content || !this.content.trim()) {
                this.contentError = true;
                rawEditor?.commands.focus();
                return;
            }
            const form = document.getElementById('customizer-form');
            form.removeAttribute('x-target');
            form.action = config.saveRoute;
            form.submit();
        },
    };
}
