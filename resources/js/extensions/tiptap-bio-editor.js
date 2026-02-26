import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Markdown } from '@tiptap/markdown';

export function createBioEditor({ element, content, onUpdate }) {
    let editor = new Editor({
        element,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2] },
                blockquote: false,
                bulletList: false,
                orderedList: false,
                codeBlock: false,
                code: false,
                horizontalRule: false,
                link: { openOnClick: false },
            }),
            Markdown.configure({ html: false }),
        ],
        content: content ?? '',
        contentType: 'markdown',
        onUpdate: ({ editor: e }) => onUpdate?.(e),
    });

    const commands = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        h2: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    };

    return {
        command(name) {
            if (commands[name]) {
                commands[name]();
                return null;
            }

            if (name === 'link') return 'link';
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

        setLink(attrs) {
            editor?.chain().focus().setLink(attrs).run();
        },

        focus() {
            editor?.commands.focus();
        },

        destroy() {
            editor?.destroy();
            editor = null;
        },
    };
}
