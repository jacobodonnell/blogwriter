import Image from '@tiptap/extension-image';
import { mergeAttributes } from '@tiptap/core';

export function createResizableImage(getEditor) {
    return Image.configure({
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
                caption: { default: null },
                width: {
                    default: null,
                    renderHTML: () => ({}),
                },
            };
        },

        addNodeView() {
            const parentFactory = this.parent?.();
            if (!parentFactory) return undefined;
            return (props) => {
                const nodeView = parentFactory(props);
                const originalHandleResize = nodeView.handleResize.bind(nodeView);
                nodeView.handleResize = (deltaX, deltaY) => {
                    originalHandleResize(deltaX, deltaY);
                    nodeView.element.style.height = 'auto';
                    const maxWidth = nodeView.container.offsetWidth;
                    const raw = parseInt(nodeView.element.style.width, 10);
                    const clamped = Math.min(raw, maxWidth);
                    nodeView.wrapper.style.width = `${clamped}px`;
                    nodeView.element.style.width = `${clamped}px`;
                };

                nodeView.onCommit = (finalWidth) => {
                    const editor = getEditor();
                    const containerWidth = nodeView.container.offsetWidth;
                    const pct = Math.round(finalWidth / containerWidth * 100);
                    const pos = nodeView.getPos?.();
                    if (pos === undefined) return;
                    if (pct >= 98) {
                        editor.chain().setNodeSelection(pos).updateAttributes('image', { width: null }).run();
                    } else {
                        editor.chain().setNodeSelection(pos).updateAttributes('image', { width: pct }).run();
                    }
                };

                const syncAttrs = (attrs) => {
                    const el = nodeView.element;
                    if (attrs.src && el.src !== attrs.src) el.src = attrs.src;
                    el.alt = attrs.alt ?? '';
                    el.style.maxWidth = '100%';
                    el.style.height = 'auto';
                    if (attrs.width) {
                        nodeView.wrapper.style.width = `${attrs.width}%`;
                        el.style.width = '100%';
                    } else {
                        nodeView.wrapper.style.width = '';
                        el.style.width = '';
                    }
                };
                syncAttrs(props.node.attrs);
                const originalUpdate = nodeView.update.bind(nodeView);
                nodeView.update = (updatedNode, decorations, innerDecorations) => {
                    const result = originalUpdate(updatedNode, decorations, innerDecorations);
                    if (result === false) return false;
                    syncAttrs(updatedNode.attrs);
                    return result;
                };
                return nodeView;
            };
        },

        renderMarkdown(node) {
            const { src = '', alt = '', title, width, caption } = node.attrs ?? {};
            const parts = [alt];
            if (width) parts.push(`width:${width}%`);
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
                if (key === 'width') attrs.width = parseInt(value, 10) || null;
                if (key === 'caption') attrs.caption = decodeURIComponent(value);
            }
            return h.createNode('image', attrs, []);
        },

        renderHTML({ HTMLAttributes }) {
            const { caption, width, align, ...rest } = HTMLAttributes;
            const imgAttrs = mergeAttributes(rest, {
                style: width ? `width:${width}%;max-width:100%` : 'max-width:100%',
            });
            if (caption) {
                return ['figure', {}, ['img', imgAttrs], ['figcaption', {}, caption]];
            }
            return ['img', imgAttrs];
        },
    });
}
