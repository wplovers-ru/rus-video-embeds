(function () {
    'use strict';

    /**
     * Applies overflow fix to embed-preview document only.
     *
     * @param {Document} doc Target sandbox document.
     */
    function applyFixToEmbedDocument(doc) {
        if (!doc || !doc.documentElement) {
            return;
        }

        // Prevent touching Gutenberg canvas or unrelated documents.
        if (!doc.querySelector('.rve-wrapper, .rve-notice')) {
            return;
        }

        doc.documentElement.style.overflow = 'hidden';

        if (doc.body) {
            doc.body.style.overflow = 'hidden';
            doc.body.style.margin = '0';
        }
    }

    /**
     * Safely applies fix to one iframe document.
     *
     * @param {HTMLIFrameElement} frame Sandbox frame.
     */
    function patchFrame(frame) {
        if (!frame) {
            return;
        }

        try {
            if (frame.contentDocument) {
                applyFixToEmbedDocument(frame.contentDocument);
            }
        } catch (e) {
            // Ignore inaccessible frames.
        }
    }

    /**
     * Patches sandbox preview iframes in a given document.
     *
     * @param {Document} doc Source document.
     */
    function patchSandboxFramesInDocument(doc) {
        if (!doc) {
            return;
        }

        var frames = doc.querySelectorAll('iframe.components-sandbox');
        frames.forEach(function (frame) {
            patchFrame(frame);
        });
    }

    /**
     * Runs patch pass for top admin document and editor canvas document.
     */
    function patchAll() {
        patchSandboxFramesInDocument(document);

        var editorCanvas = document.querySelector('iframe[title="Editor canvas"]') || document.querySelector('iframe[name="editor-canvas"]');
        if (!editorCanvas) {
            return;
        }

        try {
            if (editorCanvas.contentDocument) {
                patchSandboxFramesInDocument(editorCanvas.contentDocument);
            }
        } catch (e) {
            // Ignore inaccessible canvas states.
        }
    }

    function run() {
        // Only parent admin/editor page should orchestrate patches.
        if (window.self !== window.top) {
            return;
        }

        patchAll();

        // Gutenberg recreates preview iframes dynamically, so we run a light
        // periodic patch to catch late-created sandbox documents.
        window.setInterval(patchAll, 400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
