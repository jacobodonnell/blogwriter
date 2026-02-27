@props(['article'])

<div class="space-y-4 min-w-0 max-w-3xl">

    {{-- Title --}}
    <fieldset class="fieldset">
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

    {{-- Slug --}}
    <fieldset class="fieldset">
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
    <fieldset class="fieldset">
        <legend class="fieldset-legend">
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

        <div :class="!editorReady && 'h-0 overflow-hidden'">
            {{-- Tiptap toolbar --}}
            <div class="tiptap-toolbar flex flex-wrap items-center gap-1 p-2 bg-base-200 border border-base-content/20 border-b-0 rounded-t-field">
                <template x-if="!markdownMode">
                    <div class="flex flex-wrap items-center gap-1 flex-1">
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
                                class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Image">
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
                        <div class="divider divider-horizontal mx-0"></div>
                    </div>
                </template>
                <button type="button"
                        @click="toggleMarkdownMode()"
                        :class="[markdownMode ? 'btn-active' : '', markdownMode ? 'tooltip-right' : 'tooltip-left']"
                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Markdown Source" data-test="toolbar-markdown-source">
                    <i class="ph ph-code"></i>
                </button>
            </div>

            {{-- Contextual image toolbar (shows when figure is selected) --}}
            <div x-show="isActive('figure') && !markdownMode" x-cloak
                 class="flex items-center gap-1 px-2 py-1 bg-base-200 border border-base-content/20 border-t-0 border-b-0">
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
            <div x-show="showLinkDialog" class="flex gap-2 mt-2 items-center">
                <input x-model="linkUrl" type="url" id="link-url" placeholder="https://..." class="input input-sm input-bordered flex-1" data-test="link-url-input" @keydown.enter.prevent="insertLink()">
                <button type="button" @click="insertLink()" class="btn btn-sm btn-primary" data-test="link-insert-btn">Insert</button>
                <button type="button" @click="showLinkDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
            </div>
            <div x-show="showImageDialog" class="mt-2 p-3 border border-base-content/20 rounded-field bg-base-50 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium" x-text="editingImage ? 'Edit Image' : 'Insert Image'"></span>
                </div>
                <div class="flex gap-2 items-center">
                    <input x-model="imageUrl" type="url" id="image-url" placeholder="Image URL (https://...)" class="input input-sm input-bordered flex-1" data-test="image-url-input" @keydown.enter.prevent="insertImage()">
                </div>
                <div class="flex gap-2">
                    <input x-model="imageAlt" type="text" id="image-alt" placeholder="Alt text" class="input input-sm input-bordered flex-1" data-test="image-alt-input">
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="editingImage">
                        <button type="button" @click="removeImage()"
                                class="btn btn-sm btn-ghost text-error" data-test="image-remove-btn">
                            <i class="ph ph-trash"></i> Remove
                        </button>
                    </template>
                    <div class="flex-1"></div>
                    <button type="button" @click="insertImage()" class="btn btn-sm btn-primary" data-test="image-insert-btn">
                        <span x-text="editingImage ? 'Update' : 'Insert'"></span>
                    </button>
                    <button type="button" @click="showImageDialog = false; editingImage = false" class="btn btn-sm btn-ghost">Cancel</button>
                </div>
            </div>
            <div x-show="showYoutubeDialog" class="flex gap-2 mt-2 items-center">
                <input x-model="youtubeUrl" type="url" id="youtube-url" placeholder="YouTube URL..." class="input input-sm input-bordered flex-1" data-test="youtube-url-input" @keydown.enter.prevent="insertYoutube()">
                <button type="button" @click="insertYoutube()" class="btn btn-sm btn-primary" data-test="youtube-embed-btn">Embed</button>
                <button type="button" @click="showYoutubeDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
            </div>

            {{-- Tiptap editor mount point --}}
            <div x-show="!markdownMode">
                <div x-ref="contentEditor" data-test="content-editor"
                     :style="{ height: editorHeight }"
                     class="tiptap-editor @error('content') ring-2 ring-error @enderror border border-base-content/20 bg-base-100 min-h-64 max-h-[80vh] overflow-y-auto resize-y focus-within:outline-2 focus-within:outline-primary/20"></div>

                {{-- Word count status bar --}}
                <div class="flex justify-end px-2 py-1 border border-base-content/20 border-t-0 bg-base-200 rounded-b-field text-xs text-base-content/50"
                     x-show="editorReady" x-cloak>
                    <span x-text="wordCount + ' words'"></span>
                </div>
            </div>

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

    {{-- Summary --}}
    <fieldset class="fieldset">
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
