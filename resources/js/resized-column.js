document.addEventListener("alpine:init", () => {
    Alpine.data("resizedColumn", function ({ tableKey, columnId, cellPrefix, minColumnWidth, maxColumnWidth, sessionBrowser = 0 }) {

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
            debounceTime: 500,
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
                this.column.classList.add("relative", "group/column-resize");
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
            },

            startResize(event) {
                event.preventDefault();
                this.handleBar.classList.add("active");

                // Store initial measurements
                this.originalWidth = Math.round(this.column.offsetWidth);
                this.originalTableWidth = Math.round(this.table.offsetWidth);
                this.originalWrapperWidth = Math.round(this.tableWrapper.offsetWidth);
                const startX = event.pageX;

                const onMouseMove = (moveEvent) => {
                    if (moveEvent.pageX === startX) return;

                    const offsetX = moveEvent.pageX - startX;
                    this.currentWidth = Math.max(
                        this.minColumnWidth,
                        Math.min(
                            this.maxColumnWidth,
                            this.originalWidth + offsetX - 16 // Adjust for handle width
                        )
                    );

                    const newTableWidth = this.originalTableWidth - this.originalWidth + this.currentWidth;
                    this.table.style.width = newTableWidth > this.originalWrapperWidth
                        ? `${newTableWidth}px`
                        : "auto";

                    this.applyColumnWidth(this.currentWidth);
                    this.$dispatch("column-resized");
                };

                const onMouseUp = () => {
                    this.handleBar.classList.remove("active");
                    this.debounce(() => {
                        if (sessionBrowser) {
                            this.saveWidthToStorage(this.currentWidth);
                        } else {
                            this.$wire.updateColumnWidth(columnId, this.currentWidth);
                        }
                    }, this.debounceTime)();

                    document.removeEventListener("mousemove", onMouseMove);
                    document.removeEventListener("mouseup", onMouseUp);
                };

                document.addEventListener("mousemove", onMouseMove);
                document.addEventListener("mouseup", onMouseUp);
            },

            applyColumnWidth(width) {
                this.setColumnStyles(this.column, width);

                const cellSelector = `.${this.escapeCssClass(this.tableBodyCellPrefix + cellPrefix)}`;
                this.table.querySelectorAll(cellSelector).forEach(cell => {
                    this.setColumnStyles(cell, width);
                    cell.style.overflow = "hidden";
                });
            },

            setColumnStyles(element, width) {
                element.style.width = `${width}px`;
                element.style.minWidth = `${width}px`;
                element.style.maxWidth = `${width}px`;
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
                try {
                    sessionStorage.setItem(
                        this.getStorageKey(),
                        Math.max(
                            this.minColumnWidth,
                            Math.min(this.maxColumnWidth, width)
                        ).toString()
                    );
                } catch (e) {
                    console.error("Failed to save column width:", e);
                }
            },
        };
    });
});
