import { createBioEditor } from '../extensions/tiptap-bio-editor';

export default function bioEditor(config) {
    return {
        content: config.content ?? '',
        editorReady: false,
        showLinkDialog: false,
        linkUrl: '',

        init() {
            this._editor = createBioEditor({
                element: this.$refs.bioEditor,
                content: this.content,
                onUpdate: (editor) => {
                    this.content = editor.getMarkdown();
                },
            });

            this.editorReady = true;
        },

        destroy() {
            this._editor?.destroy();
        },

        command(name) {
            const result = this._editor?.command(name);

            if (result === 'link') {
                this.linkUrl = this._editor.getAttributes('link').href ?? '';
                this.showLinkDialog = true;
            }
        },

        isActive(name, attrs) {
            return this._editor?.isActive(name, attrs) ?? false;
        },

        insertLink() {
            if (this.linkUrl) {
                this._editor.setLink({ href: this.linkUrl });
            }

            this.showLinkDialog = false;
            this.linkUrl = '';
            this._editor?.focus();
        },
    };
}
