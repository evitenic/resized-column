document.addEventListener("alpine:init", () => {
    Alpine.data("resizedColumn", function ({ tableKey, columnId, cellName, minColumnWidth, maxColumnWidth, sessionBrowser = 0 }) {

        maxColumnWidth = maxColumnWidth === -1 ? Infinity : maxColumnWidth

        return {
            tableWrapper: null,
            table: null,
            column: null,
            minColumnWidth: minColumnWidth,
            maxColumnWidth: maxColumnWidth,
            handleBar: null,
            tableWrapperContentSelector: ".fi-ta-content",
            tableSelector: ".fi-ta-table",
            tableBodyCellPrefix: "fi-table-cell-",
            debounceTime: 0,
            currentWidth: 0,
            originalWidth: 0,
            originalTableWidth: 0,
            originalWrapperWidth: 0,

            init() {
                this.column = this.$el;
                this.table = this.column.closest(this.tableSelector);
                this.tableWrapper = this.column.closest(this.tableWrapperContentSelector);

                if (!this.column || !this.table || !this.tableWrapper) return;

                this.initializeColumnLayout();
                this.setupLivewireUpdates();
            },

            initializeColumnLayout() {
                this.column.classList.add("relative", "group/column-resize", "overflow-hidden");
                this.createHandleBar();
                const savedWidth = this.getSavedWidth();
                if (savedWidth) {
                    this.applyColumnWidth(savedWidth);
                }
            },

            createHandleBar() {
                // Remove existing handle if present
                const existingHandle = this.column.querySelector(".column-resize-handle-bar");
                if (existingHandle) existingHandle.remove();

                this.handleBar = document.createElement("button");
                this.handleBar.type = "button";
                this.handleBar.classList.add("column-resize-handle-bar");
                this.handleBar.title = "Resize column";

                this.column.appendChild(this.handleBar);
                this.handleBar.addEventListener("mousedown", (e) => this.startResize(e));

                this.handleBar.addEventListener("dblclick", (e) => this.handleDoubleClick(e));
            },

            handleDoubleClick(event) {
                event.preventDefault();
                event.stopPropagation();
                this.applyColumnWidth(100);
                this.handleColumnUpdate(100);
            },

            startResize (event) {
                event.preventDefault();
                this.handleBar.classList.add("active");

                const startX = event.pageX;
                const originalColumnWidth = Math.round(this.column.offsetWidth);
                const originalTableWidth = Math.round(this.table.offsetWidth);
                const originalWrapperWidth = Math.round(this.tableWrapper.offsetWidth);


                const onMouseMove = (moveEvent) => {
                    if (moveEvent.pageX === startX) return; // Skip if no movement

                    const delta = moveEvent.pageX - startX;

                    this.currentWidth = Math.round(
                        Math.min(
                            this.maxColumnWidth,
                            Math.max(this.minColumnWidth, originalColumnWidth + delta - 16)
                        )
                    );

                    const newTableWidth = originalTableWidth - originalColumnWidth + this.currentWidth;

                    this.table.style.width = newTableWidth > originalWrapperWidth
                        ? `${newTableWidth}px`
                        : "auto";

                    this.applyColumnWidth(this.currentWidth);
                    if(!sessionBrowser) this.$dispatch("column-resized");
                };

                const onMouseUp = () => {
                    this.handleBar.classList.remove("active");

                    if(this.debounceTime > 0) {
                        this.debounce(() => this.handleColumnUpdate(), this.debounceTime)();
                    } else {
                        this.handleColumnUpdate();
                    }

                    document.removeEventListener("mousemove", onMouseMove);
                    document.removeEventListener("mouseup", onMouseUp);
                };

                document.addEventListener("mousemove", onMouseMove);
                document.addEventListener("mouseup", onMouseUp);
            },

            handleColumnUpdate(width = this.currentWidth) {
                if (sessionBrowser) {
                    this.saveWidthToStorage(width);
                } else {
                    this.$wire.updateColumnWidth(columnId, width);
                }
            },

            applyColumnWidth(width) {
                this.setColumnStyles(this.column, width);

                const cellSelector = `.${this.escapeCssClass(this.tableBodyCellPrefix + cellName)}`;
                this.table.querySelectorAll(cellSelector).forEach(cell => {
                    this.setColumnStyles(cell, width);
                    cell.style.overflow = "hidden";
                });
            },

            setColumnStyles(element, width) {
                element.style.width = width ? `${width}px` : 'auto';
                element.style.minWidth = width ? `${width}px` : 'auto';
                element.style.maxWidth = width ? `${width}px` : 'auto';
            },

            escapeCssClass(className) {
                return className.replace(/\./g, "\\.");
            },

            debounce(callback, delay) {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        callback(...args);
                    }, delay);
                };
            },

            setupLivewireUpdates() {
                window.Livewire.hook("morph.updated", () => {
                    this.initializeColumnLayout();
                });
            },

            getStorageKey() {
                return `${tableKey}_columnWidth_${columnId}`;
            },

            getSavedWidth() {
                const savedWidth = sessionStorage.getItem(this.getStorageKey());
                return savedWidth ? parseInt(savedWidth) : null;
            },

            saveWidthToStorage(width) {
                sessionStorage.setItem(
                        this.getStorageKey(),
                        Math.max(
                            this.minColumnWidth,
                            Math.min(this.maxColumnWidth, width)
                        ).toString()
                    );
            },
        };
    });
});
