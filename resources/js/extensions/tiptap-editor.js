import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Youtube from '@tiptap/extension-youtube';
import { Markdown } from '@tiptap/markdown';
import { createResizableImage } from './resizable-image';

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
            Markdown.configure({ html: false, transformPastedText: true }),
        ],
        content: content ?? '',
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
