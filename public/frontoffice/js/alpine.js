// Toast notifications
document.addEventListener('alpine:init', () => {
    Alpine.data('toast', () => ({
        show: false,
        message: '',
        type: 'success',
        init() {
            this.$watch('show', value => {
                if (value) {
                    setTimeout(() => {
                        this.show = false;
                    }, 5000);
                }
            });
        },
        notify(message, type = 'success') {
            this.message = message;
            this.type = type;
            this.show = true;
        }
    }));
});

// Dropdown menu
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }));
});

// Modal
document.addEventListener('alpine:init', () => {
    Alpine.data('modal', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }));
});

// Form validation
document.addEventListener('alpine:init', () => {
    Alpine.data('form', () => ({
        errors: {},
        validate() {
            this.errors = {};
            const form = this.$el;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value) {
                    this.errors[input.name] = 'This field is required';
                }
            });
            
            return Object.keys(this.errors).length === 0;
        },
        submit() {
            if (this.validate()) {
                this.$el.submit();
            }
        }
    }));
});

// Table sorting
document.addEventListener('alpine:init', () => {
    Alpine.data('table', () => ({
        sortColumn: null,
        sortDirection: 'asc',
        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            const rows = Array.from(this.$el.querySelectorAll('tbody tr'));
            rows.sort((a, b) => {
                const aValue = a.querySelector(`td[data-column="${column}"]`).textContent;
                const bValue = b.querySelector(`td[data-column="${column}"]`).textContent;
                return this.sortDirection === 'asc' 
                    ? aValue.localeCompare(bValue)
                    : bValue.localeCompare(aValue);
            });
            
            const tbody = this.$el.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        }
    }));
});

// Dark mode toggle
document.addEventListener('alpine:init', () => {
    Alpine.data('darkMode', () => ({
        dark: localStorage.getItem('darkMode') === 'true',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('darkMode', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }));
});

// Mobile menu
document.addEventListener('alpine:init', () => {
    Alpine.data('mobileMenu', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }));
});

// Search functionality
document.addEventListener('alpine:init', () => {
    Alpine.data('search', () => ({
        query: '',
        results: [],
        search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            
            // Implement your search logic here
            // This is just a placeholder
            this.results = [
                { id: 1, title: 'Result 1' },
                { id: 2, title: 'Result 2' },
                { id: 3, title: 'Result 3' }
            ];
        }
    }));
}); 