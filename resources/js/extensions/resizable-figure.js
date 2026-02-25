import { Node, mergeAttributes, ResizableNodeView } from '@tiptap/core';

export function createResizableFigure(getEditor) {
    return Node.create({
        name: 'figure',
        markdownTokenName: 'image',
        group: 'block',
        content: 'inline*',
        draggable: true,
        isolating: true,

        addAttributes() {
            return {
                src: { default: null },
                alt: { default: null },
                title: { default: null },
                width: { default: null },
            };
        },

        parseHTML() {
            return [
                {
                    tag: 'figure',
                    contentElement: 'figcaption',
                    getAttrs(dom) {
                        const img = dom.querySelector('img');
                        if (!img) return false;
                        return {
                            src: img.getAttribute('src'),
                            alt: img.getAttribute('alt'),
                            title: img.getAttribute('title'),
                        };
                    },
                },
                {
                    tag: 'img[src]',
                    getAttrs(dom) {
                        return {
                            src: dom.getAttribute('src'),
                            alt: dom.getAttribute('alt'),
                            title: dom.getAttribute('title'),
                        };
                    },
                },
            ];
        },

        renderHTML({ HTMLAttributes }) {
            const { width, ...rest } = HTMLAttributes;
            const imgAttrs = mergeAttributes(rest, {
                style: width ? `width:${width}%;max-width:100%` : 'max-width:100%',
            });
            return ['figure', {}, ['img', imgAttrs], ['figcaption', 0]];
        },

        renderMarkdown(node, { renderChildren }) {
            const { src = '', alt = '', title, width } = node.attrs ?? {};
            const captionText = (node.content ? renderChildren(node.content) : node.textContent ?? '').trim();
            const parts = [alt];
            if (width) parts.push(`width:${width}%`);
            if (captionText) parts.push(`caption:\`${captionText}\``);
            const altStr = parts.join('|');
            return title ? `![${altStr}](${src} "${title}")` : `![${altStr}](${src})`;
        },

        parseMarkdown(token, h) {
            const parts = (token.text ?? '').split('|');
            const alt = parts[0] ?? '';
            const attrs = { src: token.href, alt, title: token.title ?? null };
            const content = [];
            for (const part of parts.slice(1)) {
                const colonIdx = part.indexOf(':');
                if (colonIdx === -1) continue;
                const key = part.slice(0, colonIdx).trim();
                const value = part.slice(colonIdx + 1).trim();
                if (key === 'width') attrs.width = parseInt(value, 10) || null;
                if (key === 'caption') {
                    const caption = value.startsWith('`') && value.endsWith('`')
                        ? value.slice(1, -1)
                        : value;
                    if (caption) {
                        content.push(h.createTextNode(caption));
                    }
                }
            }
            return h.createNode('figure', attrs, content);
        },

        addCommands() {
            return {
                setFigure: (attrs) => ({ commands }) => {
                    return commands.insertContent({
                        type: 'figure',
                        attrs,
                    });
                },
            };
        },

        addNodeView() {
            return ({ node, getPos, editor, HTMLAttributes }) => {
                // Build the DOM: figure > [resize-container > wrapper > img + handles] + figcaption
                const figure = document.createElement('figure');

                const img = document.createElement('img');
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                if (HTMLAttributes.src) img.src = HTMLAttributes.src;
                img.alt = HTMLAttributes.alt ?? '';
                if (HTMLAttributes.title) img.title = HTMLAttributes.title;

                const figcaption = document.createElement('figcaption');
                figcaption.setAttribute('data-placeholder', 'Add a caption…');

                const resizableNodeView = new ResizableNodeView({
                    element: img,
                    contentElement: figcaption,
                    editor,
                    node,
                    getPos,
                    onResize: (width) => {
                        img.style.width = `${width}px`;
                        img.style.height = 'auto';

                        // Clamp to container width
                        const containerWidth = resizableNodeView.container.offsetWidth;
                        if (width > containerWidth) {
                            resizableNodeView.wrapper.style.width = `${containerWidth}px`;
                            img.style.width = `${containerWidth}px`;
                        }
                    },
                    onCommit: (finalWidth) => {
                        const containerWidth = resizableNodeView.container.offsetWidth;
                        const pct = Math.round(finalWidth / containerWidth * 100);
                        const pos = getPos();
                        if (pos === undefined) return;
                        const editor_ = getEditor();
                        if (pct >= 98) {
                            editor_.chain().setNodeSelection(pos).updateAttributes('figure', { width: null }).run();
                        } else {
                            editor_.chain().setNodeSelection(pos).updateAttributes('figure', { width: pct }).run();
                        }
                    },
                    onUpdate: (updatedNode) => {
                        if (updatedNode.type.name !== 'figure') return false;
                        syncAttrs(updatedNode.attrs);
                        return true;
                    },
                    options: {
                        directions: ['left', 'right'],
                        min: { width: 60 },
                        preserveAspectRatio: true,
                    },
                });

                const syncAttrs = (attrs) => {
                    if (attrs.src && img.src !== attrs.src) img.src = attrs.src;
                    img.alt = attrs.alt ?? '';
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    if (attrs.width) {
                        resizableNodeView.wrapper.style.width = `${attrs.width}%`;
                        img.style.width = '100%';
                    } else {
                        resizableNodeView.wrapper.style.width = '';
                        img.style.width = '';
                    }
                };
                syncAttrs(node.attrs);

                // Reparent: move the resize container into our figure, add figcaption
                const resizeContainer = resizableNodeView.dom;
                figure.appendChild(resizeContainer);
                figure.appendChild(figcaption);

                // Show resize container once image loads for correct initial dimensions
                img.onload = () => {
                    resizeContainer.style.visibility = '';
                    resizeContainer.style.pointerEvents = '';
                };

                return {
                    dom: figure,
                    contentDOM: figcaption,
                    update: resizableNodeView.update.bind(resizableNodeView),
                    destroy: resizableNodeView.destroy?.bind(resizableNodeView),
                    ignoreMutation: (mutation) => {
                        if (mutation.type === 'attributes' && mutation.target === figcaption) return true;
                        if (figcaption.contains(mutation.target)) return false;
                        return true;
                    },
                };
            };
        },
    });
}
