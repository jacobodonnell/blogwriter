export function makeRevisionBrowser(config, getEditor) {
    return {
        revisionPanelOpen: false,
        sidebarPanelOpen: false,
        browsingRevision: false,
        browsingIndex: -1,
        revisions: config.revisions ?? [],
        articleId: config.articleId ?? null,
        revisionShowUrl: config.revisionShowUrl ?? '',
        revisionDestroyUrl: config.revisionDestroyUrl ?? '',
        _workingContent: null,
        _workingTitle: null,
        _revisionCache: {},

        _showError(message) {
            this.$dispatch('toast:show', { message, type: 'error' });
        },

        _clearBrowsingState() {
            this.browsingRevision = false;
            this.browsingIndex = -1;
            this._workingContent = null;
            this._workingTitle = null;
        },

        _revisionUrl(template, revisionId) {
            return template.replace('__REVISION__', revisionId);
        },

        async previewRevision(index) {
            const editor = getEditor();
            if (!editor || !this.revisions[index]) return;

            if (!this.browsingRevision) {
                this._workingContent = this.content;
                this._workingTitle = this.title;
            }

            this.browsingRevision = true;
            this.browsingIndex = index;

            const rev = this.revisions[index];
            const data = await this._fetchRevisionContent(rev.id);
            if (!data) return;

            editor.setContent(data.content);
            editor.setEditable(false);
            this.title = data.title;
        },

        async restoreRevision(index) {
            const editor = getEditor();
            if (!editor || !this.revisions[index]) return;

            const rev = this.revisions[index];
            const data = await this._fetchRevisionContent(rev.id);
            if (!data) return;

            editor.setEditable(true);
            editor.setContent(data.content);
            this.content = editor.getMarkdown();
            this.title = data.title;

            this._clearBrowsingState();
            this.checkDirty();
        },

        exitRevisionBrowsing() {
            if (!this.browsingRevision) return;

            const editor = getEditor();
            if (!editor) return;

            editor.setEditable(true);
            if (this._workingContent !== null) {
                editor.setContent(this._workingContent);
                this.content = this._workingContent;
            }
            if (this._workingTitle !== null) {
                this.title = this._workingTitle;
            }

            this._clearBrowsingState();
        },

        async deleteRevision(index) {
            const rev = this.revisions[index];
            if (!rev) return;

            if (!window.confirm('Delete this revision? This cannot be undone.')) return;

            try {
                const response = await fetch(this._revisionUrl(this.revisionDestroyUrl, rev.id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    this._showError('Failed to delete revision.');
                    return;
                }

                this.revisions.splice(index, 1);
                this._revisionCache = {};
                this.exitRevisionBrowsing();
            } catch {
                this._showError('Failed to delete revision.');
            }
        },

        async _fetchRevisionContent(revisionId) {
            if (this._revisionCache[revisionId]) {
                return this._revisionCache[revisionId];
            }

            const promise = (async () => {
                const response = await fetch(this._revisionUrl(this.revisionShowUrl, revisionId));
                if (!response.ok) {
                    throw new Error('Failed to fetch revision');
                }

                return await response.json();
            })();

            this._revisionCache[revisionId] = promise;

            try {
                return await promise;
            } catch {
                delete this._revisionCache[revisionId];
                this._showError('Failed to load revision.');
                return null;
            }
        },
    };
}
