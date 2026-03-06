import { Node, mergeAttributes } from '@tiptap/core';

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
                        let width = null;
                        const style = dom.getAttribute('style') || '';
                        const match = style.match(/--figure-width:\s*(\d+)%/);
                        if (match) width = parseInt(match[1], 10) || null;
                        return {
                            src: img.getAttribute('src'),
                            alt: img.getAttribute('alt'),
                            title: img.getAttribute('title'),
                            width,
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
            const figureAttrs = width ? { style: `--figure-width:${width}%` } : {};
            const imgAttrs = mergeAttributes(rest);
            return ['figure', figureAttrs, ['img', imgAttrs], ['figcaption', 0]];
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
            return ({ node, getPos, editor }) => {
                const MIN_WIDTH = 150;

                const getContentWidth = () => {
                    const pm = editor.view.dom;
                    const style = getComputedStyle(pm);
                    return pm.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
                };

                // Build DOM: figure > container > wrapper > [handleL, img, handleR] + figcaption
                const figure = document.createElement('figure');
                figure.style.marginInline = 'auto';

                const container = document.createElement('div');
                container.setAttribute('data-resize-container', '');
                container.style.visibility = 'hidden';
                container.style.pointerEvents = 'none';

                const wrapper = document.createElement('div');
                wrapper.setAttribute('data-resize-wrapper', '');
                wrapper.style.position = 'relative';

                const handleLeft = document.createElement('div');
                handleLeft.setAttribute('data-resize-handle', 'left');
                handleLeft.style.position = 'absolute';

                const handleRight = document.createElement('div');
                handleRight.setAttribute('data-resize-handle', 'right');
                handleRight.style.position = 'absolute';

                const img = document.createElement('img');
                img.style.width = '100%';
                img.style.height = 'auto';

                img.addEventListener('click', (e) => {
                    e.preventDefault();
                    const pos = getPos();
                    if (pos === undefined) return;
                    editor.commands.setNodeSelection(pos);
                });

                wrapper.appendChild(handleLeft);
                wrapper.appendChild(img);
                wrapper.appendChild(handleRight);
                container.appendChild(wrapper);

                const figcaption = document.createElement('figcaption');
                figcaption.setAttribute('data-placeholder', 'Add a caption…');

                figure.appendChild(container);
                figure.appendChild(figcaption);

                // Sync node attrs to DOM
                const syncAttrs = (attrs) => {
                    if (attrs.src && img.src !== attrs.src) img.src = attrs.src;
                    img.alt = attrs.alt ?? '';
                    img.title = attrs.title ?? '';
                    if (attrs.width) {
                        figure.style.setProperty('--figure-width', `${attrs.width}%`);
                        figure.style.width = `${attrs.width}%`;
                        wrapper.style.width = '100%';
                    } else {
                        figure.style.removeProperty('--figure-width');
                        figure.style.width = '';
                        wrapper.style.width = '';
                    }
                    img.style.width = '100%';
                    img.style.height = 'auto';
                };
                syncAttrs(node.attrs);

                // Show container once image loads
                img.onload = () => {
                    container.style.visibility = '';
                    container.style.pointerEvents = '';
                };

                // Resize drag state
                let dragState = null;
                let onMouseMove = null;
                let onMouseUp = null;

                const cleanupDrag = () => {
                    if (onMouseMove) document.removeEventListener('mousemove', onMouseMove);
                    if (onMouseUp) document.removeEventListener('mouseup', onMouseUp);
                    document.body.style.userSelect = '';
                    onMouseMove = null;
                    onMouseUp = null;
                    dragState = null;
                };

                const startDrag = (e, direction) => {
                    e.preventDefault();
                    document.body.style.userSelect = 'none';
                    const contentWidth = getContentWidth();
                    dragState = {
                        startX: e.clientX,
                        startWidth: wrapper.offsetWidth,
                        direction,
                        contentWidth,
                    };

                    onMouseMove = (e) => {
                        if (!dragState) return;
                        let deltaX = e.clientX - dragState.startX;
                        if (dragState.direction === 'left') deltaX = -deltaX;
                        const newWidth = Math.max(MIN_WIDTH, Math.min(dragState.startWidth + deltaX, dragState.contentWidth));
                        wrapper.style.width = `${newWidth}px`;
                        figure.style.width = `${newWidth}px`;
                    };

                    onMouseUp = () => {
                        if (!dragState) return;
                        const contentWidth = getContentWidth();
                        const pct = Math.round(wrapper.offsetWidth / contentWidth * 100);
                        const pos = getPos();
                        figure.style.width = '';

                        if (pos !== undefined) {
                            const editor_ = getEditor();
                            if (pct >= 98) {
                                editor_.chain().setNodeSelection(pos).updateAttributes('figure', { width: null }).run();
                            } else {
                                editor_.chain().setNodeSelection(pos).updateAttributes('figure', { width: pct }).run();
                            }
                        }

                        cleanupDrag();
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                };

                handleLeft.addEventListener('mousedown', (e) => startDrag(e, 'left'));
                handleRight.addEventListener('mousedown', (e) => startDrag(e, 'right'));

                return {
                    dom: figure,
                    contentDOM: figcaption,
                    update: (updatedNode) => {
                        if (updatedNode.type.name !== 'figure') return false;
                        syncAttrs(updatedNode.attrs);
                        return true;
                    },
                    destroy: cleanupDrag,
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
