@props(['article'])

<div class="min-w-0" :class="mode === 'fullscreen' ? 'grid min-h-0' : 'max-w-3xl space-y-4'">

    {{-- Title (normal mode) --}}
    <fieldset class="fieldset" x-show="mode !== 'fullscreen'">
        <legend class="fieldset-legend">
            Title
            <x-draft-revert-button field="title" />
        </legend>
        <input type="text" name="title" x-model="title"
               @blur="generateSlug()"
               @input="checkDirty()"
               class="input input-bordered w-full @error('title') input-error @enderror"
               placeholder="Article title">
        @error('title')
        <span class="text-error text-sm">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Slug (hidden in fullscreen) --}}
    <fieldset class="fieldset" x-show="mode !== 'fullscreen'">
        <legend class="fieldset-legend">
            Slug
            <x-draft-revert-button field="slug" />
        </legend>
        <input type="hidden" name="slug" :value="slug">
        <div class="join w-full">
            <span class="join-item btn btn-sm btn-disabled no-animation">/articles/</span>
            <input type="text" id="display-slug" x-model="displaySlug"
                   @input="checkDirty()"
                   class="join-item input input-bordered input-sm flex-1 @error('slug') input-error @enderror"
                   placeholder="auto-generated from title">
        </div>
        @error('slug')
        <span class="text-error text-sm">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Content --}}
    <fieldset class="fieldset" :class="mode === 'fullscreen' && '!border-none !p-0 grid grid-rows-[1fr] min-h-0 overflow-hidden'">
        <legend class="fieldset-legend" x-show="mode !== 'fullscreen'">
            Content
            <x-draft-revert-button field="content" />
        </legend>

        {{-- Hidden field for form submission --}}
        <input type="hidden" name="content" :value="content">

        {{-- Skeleton placeholder while Tiptap initializes --}}
        <div x-show="!editorReady" x-transition:leave x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-2">
            <div class="skeleton h-10 w-full rounded"></div>
            <div class="skeleton h-64 w-full rounded"></div>
        </div>

        <div :class="{ 'h-0 overflow-hidden': !editorReady, 'grid grid-rows-[auto_1fr] min-h-0': mode === 'fullscreen' && editorReady }">

            {{-- Toolbar wrapper: sticky full-bleed in fullscreen, inert in normal mode --}}
            <div :class="mode === 'fullscreen'
                ? 'sticky top-0 z-20 bg-base-200'
                : ''">

            {{-- Fullscreen: single-line WYSIWYG toolbar --}}
            <div x-show="mode === 'fullscreen' && !markdownMode" x-cloak class="tiptap-toolbar">
                <div class="flex items-center gap-1 bg-base-200 p-2 px-3">
                {{-- Collapse to split panel (shows as "Editor" on mobile) --}}
                <button type="button"
                        @click="mode = 'split'"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-toolbar-split">
                    <i class="ph text-base" :class="isMobile ? 'ph-note-pencil' : 'ph-split-horizontal'"></i>
                    <span x-text="isMobile ? 'Editor' : 'Split'"></span>
                </button>
                {{-- Collapse to classic editor (desktop only) --}}
                <button type="button"
                        @click="mode = 'classic'"
                        class="btn btn-ghost btn-xs gap-1 hidden md:inline-flex" data-test="fs-toolbar-classic">
                    <i class="ph ph-frame-corners text-base"></i> Classic
                </button>
                {{-- Collapse to live preview --}}
                <button type="button"
                        @click="mode = 'preview'"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-toolbar-preview">
                    <i class="ph ph-eye text-base"></i> Preview
                </button>
                <div class="divider divider-horizontal mx-0"></div>
                <button type="button" @click="undo()" :disabled="!canUndo()"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-toolbar-undo">
                    <i class="ph ph-arrow-counter-clockwise"></i> Undo
                </button>
                <button type="button" @click="redo()" :disabled="!canRedo()"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-toolbar-redo">
                    <i class="ph ph-arrow-clockwise"></i> Redo
                </button>
                <div class="divider divider-horizontal mx-0"></div>
                <button type="button" @click="command('bold')" :class="isActive('bold') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Bold" data-test="fs-toolbar-bold">
                    <i class="ph ph-text-b"></i>
                </button>
                <button type="button" @click="command('italic')" :class="isActive('italic') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Italic" data-test="fs-toolbar-italic">
                    <i class="ph ph-text-italic"></i>
                </button>
                <div class="divider divider-horizontal mx-0"></div>
                <button type="button" @click="command('h2')" :class="isActive('heading', {level:2}) && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Heading 2" data-test="fs-toolbar-h2">H2</button>
                <button type="button" @click="command('h3')" :class="isActive('heading', {level:3}) && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Heading 3" data-test="fs-toolbar-h3">H3</button>
                <button type="button" @click="command('h4')" :class="isActive('heading', {level:4}) && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Heading 4" data-test="fs-toolbar-h4">H4</button>
                <button type="button" @click="command('h5')" :class="isActive('heading', {level:5}) && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Heading 5" data-test="fs-toolbar-h5">H5</button>
                <div class="divider divider-horizontal mx-0"></div>
                <button type="button" @click="command('blockquote')" :class="isActive('blockquote') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Blockquote" data-test="fs-toolbar-blockquote">
                    <i class="ph ph-quotes"></i>
                </button>
                <button type="button" @click="command('bulletList')" :class="isActive('bulletList') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Bullet List" data-test="fs-toolbar-bullet-list">
                    <i class="ph ph-list-bullets"></i>
                </button>
                <button type="button" @click="command('orderedList')" :class="isActive('orderedList') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Ordered List" data-test="fs-toolbar-ordered-list">
                    <i class="ph ph-list-numbers"></i>
                </button>
                <div class="divider divider-horizontal mx-0"></div>
                <button type="button" @click="command('link')"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Link" data-test="fs-toolbar-link">
                    <i class="ph ph-link"></i>
                </button>
                <button type="button" @click="command('image')"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Image" data-test="fs-toolbar-image">
                    <i class="ph ph-image"></i>
                </button>
                <button type="button" @click="command('code')" :class="isActive('code') && 'btn-active'"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Inline Code" data-test="fs-toolbar-code">
                    <i class="ph ph-code"></i>
                </button>
                <button type="button" @click="command('horizontalRule')"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="Horizontal Rule" data-test="fs-toolbar-hr">
                    <i class="ph ph-minus"></i>
                </button>
                <button type="button" @click="command('youtube')"
                        class="btn btn-ghost btn-xs btn-square tooltip tooltip-bottom" data-tip="YouTube" data-test="fs-toolbar-youtube">
                    <i class="ph ph-youtube-logo"></i>
                </button>
                <button type="button" @click="revisionPanelOpen = !revisionPanelOpen"
                        x-show="revisions.length > 0"
                        :class="revisionPanelOpen && 'btn-active'"
                        class="btn btn-ghost btn-xs gap-1 ml-auto" data-test="fs-toolbar-revisions" x-cloak>
                    <i class="ph ph-clock-counter-clockwise text-base"></i> Revisions
                </button>
                <button type="button" @click="sidebarPanelOpen = !sidebarPanelOpen"
                        :class="sidebarPanelOpen && 'btn-active'"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-toolbar-settings">
                    <i class="ph ph-gear text-base"></i> Settings
                </button>
                </div>
            </div>

            {{-- Fullscreen: markdown mode toolbar (minimal) --}}
            <div x-show="mode === 'fullscreen' && markdownMode" x-cloak class="tiptap-toolbar">
                <div class="flex items-center gap-1 bg-base-200 p-2 px-3">
                {{-- Collapse to split panel (shows as "Editor" on mobile) --}}
                <button type="button"
                        @click="mode = 'split'"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-md-toolbar-split">
                    <i class="ph text-base" :class="isMobile ? 'ph-note-pencil' : 'ph-split-horizontal'"></i>
                    <span x-text="isMobile ? 'Editor' : 'Split'"></span>
                </button>
                {{-- Collapse to classic editor (desktop only) --}}
                <button type="button"
                        @click="mode = 'classic'"
                        class="btn btn-ghost btn-xs gap-1 hidden md:inline-flex" data-test="fs-md-toolbar-classic">
                    <i class="ph ph-frame-corners text-base"></i> Classic
                </button>
                {{-- Collapse to live preview --}}
                <button type="button"
                        @click="mode = 'preview'"
                        class="btn btn-ghost btn-xs gap-1" data-test="fs-md-toolbar-preview">
                    <i class="ph ph-eye text-base"></i> Preview
                </button>
                <button type="button" @click="toggleMarkdownMode()"
                        class="btn btn-ghost btn-xs gap-1 btn-active" data-test="fs-md-toolbar-markdown">
                    <i class="ph ph-code"></i> Markdown
                </button>
                <button type="button" @click="sidebarPanelOpen = !sidebarPanelOpen"
                        :class="sidebarPanelOpen && 'btn-active'"
                        class="btn btn-ghost btn-xs gap-1 ml-auto" data-test="fs-md-toolbar-settings">
                    <i class="ph ph-gear text-base"></i> Settings
                </button>
                </div>
            </div>

            {{-- Toolbar 1: Mode toolbar (hidden in fullscreen) --}}
            <div x-show="mode !== 'fullscreen'"
                 class="tiptap-toolbar flex items-center gap-1 bg-base-200 border border-base-200 p-2 border-b-0 rounded-t-field">

                {{-- Markdown source toggle --}}
                <button type="button"
                        @click="toggleMarkdownMode()"
                        :class="markdownMode && 'btn-active'"
                        class="btn btn-ghost btn-xs gap-1 ml-auto" data-test="toolbar-markdown-source">
                    <i class="ph ph-code"></i> Markdown
                </button>
            </div>

            {{-- Toolbar 2: Formatting toolbar (hidden in markdown mode and fullscreen) --}}
            <template x-if="!markdownMode">
                <div x-show="mode !== 'fullscreen'"
                     class="tiptap-toolbar flex flex-wrap items-center gap-1 bg-base-200 border border-base-200 border-b-0 border-t-0 p-2">
                    <button type="button" @click="undo()" :disabled="!canUndo()"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Undo" data-test="toolbar-undo">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </button>
                    <button type="button" @click="redo()" :disabled="!canRedo()"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Redo" data-test="toolbar-redo">
                        <i class="ph ph-arrow-clockwise"></i>
                    </button>
                    <div class="divider divider-horizontal mx-0"></div>
                    <button type="button" @click="command('bold')" :class="isActive('bold') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Bold" data-test="toolbar-bold">
                        <i class="ph ph-text-b"></i>
                    </button>
                    <button type="button" @click="command('italic')" :class="isActive('italic') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Italic" data-test="toolbar-italic">
                        <i class="ph ph-text-italic"></i>
                    </button>
                    <div class="divider divider-horizontal mx-0"></div>
                    <button type="button" @click="command('h2')" :class="isActive('heading', {level:2}) && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 2" data-test="toolbar-h2">H2</button>
                    <button type="button" @click="command('h3')" :class="isActive('heading', {level:3}) && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 3">H3</button>
                    <button type="button" @click="command('h4')" :class="isActive('heading', {level:4}) && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 4">H4</button>
                    <button type="button" @click="command('h5')" :class="isActive('heading', {level:5}) && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 5">H5</button>
                    <div class="divider divider-horizontal mx-0"></div>
                    <button type="button" @click="command('blockquote')" :class="isActive('blockquote') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Blockquote" data-test="toolbar-blockquote">
                        <i class="ph ph-quotes"></i>
                    </button>
                    <button type="button" @click="command('bulletList')" :class="isActive('bulletList') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Bullet List" data-test="toolbar-bullet-list">
                        <i class="ph ph-list-bullets"></i>
                    </button>
                    <button type="button" @click="command('orderedList')" :class="isActive('orderedList') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Ordered List" data-test="toolbar-ordered-list">
                        <i class="ph ph-list-numbers"></i>
                    </button>
                    <div class="divider divider-horizontal mx-0"></div>
                    <button type="button" @click="command('link')"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Link">
                        <i class="ph ph-link"></i>
                    </button>
                    <button type="button" @click="command('image')"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Image" data-test="toolbar-image">
                        <i class="ph ph-image"></i>
                    </button>
                    <button type="button" @click="command('code')" :class="isActive('code') && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Inline Code">
                        <i class="ph ph-code"></i>
                    </button>
                    <button type="button" @click="command('horizontalRule')"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Horizontal Rule">
                        <i class="ph ph-minus"></i>
                    </button>
                    <button type="button" @click="command('youtube')"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="YouTube" data-test="toolbar-youtube">
                        <i class="ph ph-youtube-logo"></i>
                    </button>
                </div>
            </template>

            {{-- Contextual toolbars & inline dialogs --}}
            <div>

                {{-- Contextual image toolbar (shows when figure is selected) --}}
                <div x-show="isActive('figure') && !markdownMode" x-cloak
                     class="flex items-center gap-1 px-2 py-1 bg-base-200 border border-base-200 border-t-0 border-b-0"
                     :class="mode === 'fullscreen' && 'border-x-0'">
                    <span class="text-xs text-base-content/50 mr-1">Image:</span>
                    <button type="button" @click="command('imageFullWidth')"
                            :class="isImageFullWidth() && 'btn-active'"
                            class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Full Width">
                        <i class="ph ph-arrows-out-line-horizontal"></i>
                    </button>
                    <div class="divider divider-horizontal mx-0"></div>
                    <button type="button" @click="openEditImage()"
                            class="btn btn-ghost btn-xs tooltip" data-tip="Edit image">
                        <i class="ph ph-pencil-simple"></i> Edit
                    </button>
                    <button type="button" @click="removeImage()"
                            class="btn btn-ghost btn-xs btn-square tooltip text-error"
                            data-tip="Remove image" data-test="toolbar-remove-image">
                        <i class="ph ph-trash"></i>
                    </button>
                </div>

                {{-- Inline dialogs for link / image / youtube --}}
                <div x-show="showLinkDialog" class="flex gap-2 p-2 items-center bg-base-200 border border-base-200 border-t-0 border-b-0"
                     :class="mode === 'fullscreen' ? 'px-3 border-x-0' : ''">
                    <input x-model="linkUrl" type="url" id="link-url" placeholder="https://..." class="input input-sm input-bordered flex-1" data-test="link-url-input" @keydown.enter.prevent="insertLink()">
                    <button type="button" @click="insertLink()" class="btn btn-sm btn-primary" data-test="link-insert-btn">Insert</button>
                    <button type="button" @click="showLinkDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
                </div>
                <div x-show="showImageDialog" class="p-2 bg-base-200 border border-base-200 border-t-0 border-b-0 space-y-2"
                     :class="mode === 'fullscreen' ? 'px-3 border-x-0' : ''">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium" x-text="editingImage ? 'Edit Image' : 'Insert Image'"></span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <input x-model="imageUrl" type="url" id="image-url" placeholder="Image URL (https://...)" class="input input-sm input-bordered flex-1" data-test="image-url-input" @keydown.enter.prevent="insertImage()">
                    </div>
                    <div class="flex flex-col gap-1">
                        <input x-model="imageAlt" type="text" id="image-alt" placeholder="Alt text" required class="input input-sm input-bordered w-full" data-test="image-alt-input">
                        <span class="text-xs text-base-content/50">Required for accessibility</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="editingImage">
                            <button type="button" @click="removeImage()"
                                    class="btn btn-sm btn-ghost text-error" data-test="image-remove-btn">
                                <i class="ph ph-trash"></i> Remove
                            </button>
                        </template>
                        <button type="button" @click="insertImage()" :disabled="!imageAlt?.trim()" class="btn btn-sm btn-primary ml-auto" data-test="image-insert-btn">
                            <span x-text="editingImage ? 'Update' : 'Insert'"></span>
                        </button>
                        <button type="button" @click="showImageDialog = false; editingImage = false" class="btn btn-sm btn-ghost">Cancel</button>
                    </div>
                </div>
                <div x-show="showYoutubeDialog" class="flex gap-2 p-2 items-center bg-base-200 border border-base-200 border-t-0 border-b-0"
                     :class="mode === 'fullscreen' ? 'px-3 border-x-0' : ''">
                    <input x-model="youtubeUrl" type="url" id="youtube-url" placeholder="YouTube URL..." class="input input-sm input-bordered flex-1" data-test="youtube-url-input" @keydown.enter.prevent="insertYoutube()">
                    <button type="button" @click="insertYoutube()" class="btn btn-sm btn-primary" data-test="youtube-embed-btn">Embed</button>
                    <button type="button" @click="showYoutubeDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
                </div>

            </div>

            </div>{{-- end toolbar wrapper --}}

            {{-- In fullscreen: grid with 1fr (scroll) + auto (footer) --}}
            <div :class="mode === 'fullscreen' ? 'min-h-0 grid grid-rows-[1fr_auto]' : ''">

            <div :class="mode === 'fullscreen' ? 'min-h-0 overflow-y-auto' : ''" data-test="fs-scroll-container">

            {{-- Fullscreen inline title (below toolbars, auto-grows for wrapping) --}}
            <div x-show="mode === 'fullscreen'" x-cloak class="bg-base-100 border-x-0">
                <div class="max-w-3xl mx-auto px-6 pt-8 pb-2">
                    <textarea x-model="title"
                              @blur="generateSlug()"
                              @input="checkDirty(); $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              x-effect="if (mode === 'fullscreen') { $nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' }) }"
                              rows="1"
                              class="w-full bg-transparent border-none outline-none text-3xl font-bold text-base-content placeholder:text-base-content/30 resize-none overflow-hidden leading-tight"
                              placeholder="Article title"></textarea>
                </div>
            </div>

            {{-- Fullscreen: featured image preview (read-only) --}}
            <div x-show="mode === 'fullscreen' && featuredImagePreview" x-cloak class="bg-base-100">
                <div class="max-w-3xl mx-auto px-6 pb-4">
                    <img :src="featuredImagePreview"
                         :alt="featuredImageAlt || title || 'Featured image'"
                         class="w-full rounded-lg">
                    <p x-show="effectiveCaption"
                       class="text-sm text-base-content/60 text-center mt-2"
                       x-text="effectiveCaption"
                       x-cloak></p>
                </div>
            </div>

            {{-- Tiptap editor mount point --}}
            <div x-show="!markdownMode">
                <div x-ref="contentEditor" data-test="content-editor"
                     :style="mode === 'fullscreen' ? {} : { height: editorHeight }"
                     class="tiptap-editor @error('content') ring-2 ring-error @enderror border border-base-content/20 bg-base-100 overflow-y-auto"
                     :class="{
                         'fullscreen-tiptap !border-x-0 !border-b-0 !border-t-0 !overflow-y-visible': mode === 'fullscreen',
                         'focus-within:outline-2 focus-within:outline-primary/20': mode !== 'fullscreen',
                         'min-h-64 max-h-[80vh] resize-y': mode !== 'fullscreen',
                         'ring-2 ring-warning/30': browsingRevision,
                     }"></div>

            </div>

            </div>{{-- end fullscreen scrollable wrapper --}}

            {{-- Word count status bar --}}
            <div class="flex justify-end px-2 py-1 border border-base-content/20 border-t bg-base-200 text-xs text-base-content/50"
                 :class="mode === 'fullscreen' ? 'border-x-0 border-b-0' : 'border-t-0 rounded-b-field'"
                 x-show="editorReady && !markdownMode" x-cloak>
                <span x-show="browsingRevision" class="text-warning mr-auto" x-cloak>Viewing revision</span>
                <span x-text="wordCount + ' words'"></span>
            </div>

            </div>{{-- end grid wrapper --}}

            {{-- Raw markdown textarea (shown in source mode) --}}
            <textarea x-show="markdownMode"
                      x-model="content"
                      @input="checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))"
                      :style="{ height: editorHeight }"
                      class="textarea textarea-bordered font-mono text-sm w-full rounded-t-none"
                      spellcheck="false"
                      x-cloak></textarea>
        </div>

        @error('content')
        <div role="alert" class="alert alert-error mt-2" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 8000)" x-transition>
            <i class="ph ph-x-circle text-xl"></i>
            <span>{{ $message }}</span>
        </div>
        @enderror

        {{-- Client-side: content required warning --}}
        <div x-show="contentError" x-cloak x-transition role="alert" class="alert alert-error mt-2">
            <i class="ph ph-x-circle text-xl"></i>
            <span>Please add some content before saving.</span>
        </div>

        {{-- H1 Warning (client-side only, hidden when server already shows error) --}}
        @unless($errors->has('content'))
            <div x-show="/^# (?!#)/m.test(content)" x-cloak x-transition class="alert alert-warning mt-2">
                <i class="ph ph-warning text-xl"></i>
                <span>H1 headings (#) are not allowed — the article title is already H1. Use ## or smaller.</span>
            </div>
        @endunless
    </fieldset>

    {{-- Summary (hidden in fullscreen) --}}
    <fieldset class="fieldset" x-show="mode !== 'fullscreen'">
        <legend class="fieldset-legend">
            Summary
            <x-draft-revert-button field="summary" />
        </legend>
        <textarea name="summary" x-model="summary"
                  @input="checkDirty()"
                  data-test="summary-field"
                  class="textarea textarea-bordered w-full h-20 text-sm @error('summary') textarea-error @enderror"
                  placeholder="Auto-generated if empty">{{ old('summary', $article->summary) }}</textarea>
        @error('summary')
        <span class="text-error text-sm">{{ $message }}</span>
        @enderror
    </fieldset>

</div>
