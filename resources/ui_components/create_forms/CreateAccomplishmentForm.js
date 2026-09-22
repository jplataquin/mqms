import {Template,Component} from '/adarna.js';

class CreateAccomplishmentForm extends Component {

    model() {
        return {
            component_id: '',
            successCallback: () => {
                document.location.reload(true);
            }
        }
    }

    view() {
        const t = new Template();

        // Get today's date in YYYY-MM-DD format
        const today = new Date().toISOString().split('T')[0];

        return t.div(() => {
            t.div({class: 'row mb-3'}, () => {
                t.div({class: 'col-lg-6'}, () => {
                    t.div({class: 'form-group'}, () => {
                        t.label({class: 'form-label fw-semibold'}, 'Entry Date *');
                        this.el.entry_data = t.input({class: 'form-control', type: 'date', value: today, required: true});
                        this.el.entry_data_feedback = t.div({class: 'invalid-feedback d-none'}, 'Entry date is required.');
                    });
                });
                t.div({class: 'col-lg-6'}, () => {
                    t.div({class: 'form-group'}, () => {
                        t.label({class: 'form-label fw-semibold'}, 'Quantity *');
                        this.el.quantity = t.input({class: 'form-control', type: 'text', placeholder: '0.00', required: true});
                        this.el.quantity_feedback = t.div({class: 'invalid-feedback d-none'}, 'Quantity is required and must be numeric.');
                    });
                });
            });

            t.div({class: 'row mb-3'}, () => {
                t.div({class: 'col-lg-12'}, () => {
                    t.div({class: 'form-group'}, () => {
                        t.label({class: 'form-label fw-semibold'}, 'Remarks *');
                        this.el.remarks = t.textarea({class: 'form-control', rows: '4', placeholder: 'Enter details or remarks...', required: true});
                        this.el.remarks_feedback = t.div({class: 'invalid-feedback d-none'}, 'Remarks are required.');
                    });
                });
            });

            t.div({class: 'row mb-3'}, () => {
                t.div({class: 'col-lg-12 text-end'}, () => {
                    this.el.submit_btn = t.div({class: 'btn btn-primary me-3'}, 'Save Entry');
                    this.el.cancel_btn = t.div({class: 'btn btn-secondary'}, 'Cancel');
                });
            });
        });
    }

    controller() {
        this.el.quantity.onkeypress = (e) => {
            return window.util.inputNumber(this.el.quantity, e, 4, false);
        }

        this.el.submit_btn.onclick = () => {
            this.submit();
        }

        this.el.cancel_btn.onclick = () => {
            window.util.drawerModal.close();
        }
    }

    submit() {
        let hasError = false;

        // Reset validations
        this.el.entry_data.classList.remove('is-invalid');
        this.el.quantity.classList.remove('is-invalid');
        this.el.remarks.classList.remove('is-invalid');

        this.el.entry_data_feedback.classList.add('d-none');
        this.el.quantity_feedback.classList.add('d-none');
        this.el.remarks_feedback.classList.add('d-none');

        if (!this.el.entry_data.value) {
            this.el.entry_data.classList.add('is-invalid');
            this.el.entry_data_feedback.classList.remove('d-none');
            hasError = true;
        }

        if (!this.el.quantity.value || isNaN(this.el.quantity.value)) {
            this.el.quantity.classList.add('is-invalid');
            this.el.quantity_feedback.classList.remove('d-none');
            hasError = true;
        }

        if (!this.el.remarks.value || this.el.remarks.value.trim() === '') {
            this.el.remarks.classList.add('is-invalid');
            this.el.remarks_feedback.classList.remove('d-none');
            hasError = true;
        }

        if (hasError) {
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/accomplishment/add', {
            component_id: this._model.component_id,
            entry_data: this.el.entry_data.value,
            quantity: this.el.quantity.value,
            remarks: this.el.remarks.value
        }).then(reply => {
            window.util.unblockUI();

            if (reply.status <= 0) {
                window.util.showMsg(reply);
                return false;
            }

            window.util.drawerModal.close();
            this._model.successCallback(reply.data);
        });
    }
}

export default (data) => {
    return (new CreateAccomplishmentForm(data));
}
