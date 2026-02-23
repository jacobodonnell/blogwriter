import { Editor, Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Slice } from '@tiptap/pm/model';
import StarterKit from '@tiptap/starter-kit';
import Youtube from '@tiptap/extension-youtube';
import { Markdown } from '@tiptap/markdown';
import { createResizableImage } from './resizable-image';

/**
 * Normalize markdown text: collapse CRLF, normalize excessive newlines,
 * and expand single newlines between non-blank lines to paragraph breaks.
 * Code blocks are left untouched.
 */
function normalizeMarkdown(text) {
    if (!text) return text;
    text = text.replace(/\r\n/g, '\n');
    const parts = text.split(/(```[\s\S]*?```)/g);
    return parts.map((part, i) => {
        if (i % 2 !== 0) return part;
        part = part.replace(/\n{3,}/g, '\n\n');
        part = part.replace(/([^\n])\n([^\n])/g, '$1\n\n$2');
        return part;
    }).join('');
}

const MarkdownPaste = Extension.create({
    name: 'markdownPaste',
    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('markdownPaste'),
                props: {
                    handlePaste: (view, event) => {
                        const clipboardData = event.clipboardData;
                        if (!clipboardData) return false;
                        const html = clipboardData.getData('text/html');
                        const text = clipboardData.getData('text/plain');
                        if (!text) return false;
                        const { $from } = view.state.selection;
                        if ($from.parent.type.spec.code) return false;
                        const json = this.editor.markdown.parse(normalizeMarkdown(text));
                        const node = this.editor.state.schema.nodeFromJSON(json);
                        const slice = new Slice(node.content, 0, 0);
                        view.dispatch(view.state.tr.replaceSelection(slice).scrollIntoView());
                        return true;
                    },
                },
            }),
        ];
    },
});

export function createTiptapEditor({ element, content, onUpdate, onSelectionUpdate }) {
    let editor = new Editor({
        element,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4, 5] },
                codeBlock: { languageClassPrefix: 'language-' },
                link: { openOnClick: false },
            }),
            createResizableImage(() => editor),
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
            Markdown.configure({ html: false }),
            MarkdownPaste,
        ],
        content: normalizeMarkdown(content) ?? '',
        contentType: 'markdown',
        onUpdate: ({ editor: e }) => onUpdate?.(e),
        onSelectionUpdate: () => onSelectionUpdate?.(),
    });

    const commands = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        h2: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        h3: () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
        h4: () => editor.chain().focus().toggleHeading({ level: 4 }).run(),
        h5: () => editor.chain().focus().toggleHeading({ level: 5 }).run(),
        blockquote: () => editor.chain().focus().toggleBlockquote().run(),
        bulletList: () => editor.chain().focus().toggleBulletList().run(),
        orderedList: () => editor.chain().focus().toggleOrderedList().run(),
        code: () => editor.chain().focus().toggleCode().run(),
        codeBlock: () => editor.chain().focus().toggleCodeBlock().run(),
        horizontalRule: () => editor.chain().focus().setHorizontalRule().run(),
        imageFullWidth: () => editor.chain().focus().updateAttributes('image', { width: null }).run(),
    };

    return {
        command(name) {
            if (commands[name]) {
                commands[name]();
                return null;
            }
            // Return signal for dialog-opening commands
            if (name === 'link' || name === 'image' || name === 'youtube') {
                return name;
            }
            return null;
        },

        isActive(name, attrs) {
            return editor?.isActive(name, attrs) ?? false;
        },

        getAttributes(name) {
            return editor?.getAttributes(name) ?? {};
        },

        getMarkdown() {
            return editor?.getMarkdown() ?? '';
        },

        focus() {
            editor?.commands.focus();
        },

        setLink(attrs) {
            editor?.chain().focus().setLink(attrs).run();
        },

        setImage(attrs) {
            editor?.chain().focus().setImage(attrs).run();
        },

        updateImageAttributes(attrs) {
            editor?.chain().focus().updateAttributes('image', attrs).run();
        },

        setYoutubeVideo(attrs) {
            editor?.chain().focus().setYoutubeVideo(attrs).run();
        },

        get wordCount() {
            const text = editor?.state.doc.textContent.trim();
            if (!text) return 0;
            return text.split(/\s+/).length;
        },

        destroy() {
            editor?.destroy();
            editor = null;
        },
    };
}
