import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { ExpenseService } from 'src/app/shared/services/expense.service';
import { IncomeService } from 'src/app/shared/services/income.service';
import { environment } from 'src/environments/environment';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-detail-budget',
  standalone: false,
  templateUrl: './detail-budget.component.html',
  styleUrl: './detail-budget.component.scss'
})
export class DetailBudgetComponent implements OnInit {
  // bread crumb items
  breadCrumbItems!: Array<{}>;
  public Collapsed = false;
  public firstCollapse = false;
  public secondCollapse = false;
  public bothColleaps = false;

  id!: any; budget: any; is_loading: boolean = false; incomes: any[] = []; expense_form!: FormGroup; expenses: any[] = []; income_form!: FormGroup;

  repartitions: any; reps: any; is_processing: boolean = false; p: number = 1; expenses_by_cat: any[] = []; global_event: any; qte_values$: any; price_values$: any;

  message: string = ''; today!: Date; management_units: any; with_detail: boolean = false; total_amount_used_for_budget: number = 0; invoice: any; income_dialog: boolean = false;

  domain_url: string = environment.domainUrl; categories: any; barData: any; barOptions: any; total_fixed_charges: number = 0; total_variable_charges: number = 0; total_sum_incomes: number = 0;

  total_saving_charges: number = 0; budget_incomes: any; file_url: any; detail_dialog: boolean = false; expense_det: any; total_fixed_sum_used_by_rep: number = 0; total_fixed_balance_by_rep: number = 0;

  total_variable_sum_used_by_rep: number = 0; total_variable_balance_by_rep: number = 0; total_saving_sum_used_by_rep: number = 0; total_saving_balance_by_rep: number = 0;

  repartition_expense: any; p2: number = 1; exp_budget: any;
  
  constructor(private router: Router, private route: ActivatedRoute, private fb: FormBuilder, private income_service: IncomeService,
    private budget_service: BudgetService, private modalService: NgbModal, private expense_service: ExpenseService, private toastr: ToastrService
  ) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Détails d\'un budget', active: true }
    ];

    this.id = this.route.snapshot.params['id'];
    this.budgetDetail();
    this.today = new Date();
    this.listIncomes();
    this.listUnitsManag();
    this.initExpenseForm();
  }

  private updateExpenseAmount() {
    let qte = this.expense_form.get('quantity')?.value;
    let price = this.expense_form.get('unit_price')?.value;

    let total = qte * price;

    this.expense_form.patchValue({
      exp_amount: total
    });
  }

  initExpenseForm() {
    this.expense_form = this.fb.group({
      id: [''],
      budget_id: [null, [Validators.required]],
      repartition_id: [null, [Validators.required]],
      exp_amount: [{ value: null, disabled: false }, [Validators.required]],
      expense_date: [null, [Validators.required]],
      comment: [''],
      quantity: [null],
      unit_price: [null],
      management_unit_id: [null],
      invoice_path: [null]
    });

    this.qte_values$ = this.expense_form.get('quantity')?.valueChanges;
    this.price_values$ = this.expense_form.get('unit_price')?.valueChanges;

    this.qte_values$.subscribe(() => {
      this.updateExpenseAmount();
    });

    this.price_values$.subscribe(() => {
      this.updateExpenseAmount();
    });
  }

  displayModal(status: boolean) {
    if (status) {
      this.income_dialog = true;
    } else {
      this.income_dialog = false;
      this.income_form.reset();
    }
  }

  listUnitsManag() {
    this.expense_service.listUnitsManagement().subscribe({
      next: (res: any) => {
        this.management_units = res.data;
      }
    });
  }

  getRepId() {
    this.expense_form.patchValue({
      budget_id: this.budget?.id
    });
  }

  budgetDetail() {
    this.is_loading = true;
    this.budget_service.show({ 'id': this.id }).subscribe(
      {
        next: (v: any) => {
          this.budget = v.data.budget;
          this.categories = v.data.categories;
          this.total_amount_used_for_budget = v.data.total_amount_used;
          this.incomes = v.data.incomes;
          this.repartitions = v.data.others_data;
          this.exp_budget = v.data.exp_budget;
          console.log(this.repartitions)
          this.total_sum_incomes = v.data.total_sum_incomes_recup;
          this.total_fixed_charges = v.data.total_sum_cf_recup;
          this.total_variable_charges = v.data.total_sum_cv_recup;
          this.total_saving_charges = v.data.total_sum_saving_recup;
          this.total_fixed_sum_used_by_rep = v.data.total_fixed_sum_used_by_rep;
          this.total_variable_sum_used_by_rep = v.data.total_variable_sum_used_by_rep;
          this.total_saving_sum_used_by_rep = v.data.total_saving_sum_used_by_rep;
          this.total_fixed_balance_by_rep = v.data.total_fixed_balance_by_rep;
          this.total_variable_balance_by_rep = v.data.total_variable_balance_by_rep;
          this.total_saving_balance_by_rep = v.data.total_saving_balance_by_rep;

          this.is_loading = false;
        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }

      });
  }

  getValueOfSwith(e: any) {
    this.with_detail = e.target.checked;
    const qte_control = this.expense_form.get('quantity');
    const price_control = this.expense_form.get('unit_price');
    if (this.with_detail) {
      this.expense_form.get('exp_amount')?.disable();
      qte_control?.setValidators(Validators.required);
      price_control?.setValidators(Validators.required);
    } else {
      this.expense_form.get('exp_amount')?.enable();
      qte_control?.clearValidators();
      price_control?.clearValidators();
    }
    qte_control?.updateValueAndValidity();
    price_control?.updateValueAndValidity();
  }

  closeForm(){
    this.modalService.dismissAll();
    this.expense_form.reset();
  }


  listIncomes() {
    this.income_service.list().subscribe({
      next: (v: any) => {
        this.budget_incomes = v.data;
      }
    });
  }

  detailExpensesByCategory(exlargeModal: any, repartition: any) {
    console.log(repartition)
    this.repartition_expense = repartition;
    this.modalService.open(exlargeModal, { size: 'xl', windowClass: 'modal-holder', centered: true });
  }

  
  openExpenseModal(contentExpense: any) {
    this.modalService.open(contentExpense, { centered: true });
  }

  addExpense() {
    this.is_processing = true;
    const formData = new FormData();
    if (this.expense_form.value.id) {
      formData.append('id', this.expense_form.get('id')?.value);
    }

    formData.append('budget_id', this.expense_form.get('budget_id')?.value);
    formData.append('repartition_id', this.expense_form.get('repartition_id')?.value);
    formData.append('exp_amount', this.expense_form.get('exp_amount')?.value);
    formData.append('quantity', this.expense_form.get('quantity')?.value);
    formData.append('comment', this.expense_form.get('comment')?.value);
    formData.append('unit_price', this.expense_form.get('unit_price')?.value);
    formData.append('comment', this.expense_form.get('comment')?.value);
    formData.append('expense_date', this.expense_form.get('expense_date')?.value);
    formData.append('management_unit_id', this.expense_form.get('management_unit_id')?.value);
    formData.append('invoice_path', this.invoice);
    this.expense_service.create(formData).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.expense_form.reset();
            this.with_detail = false;
            if(this.invoice){
              this.resetInputFile();
            }
            this.showSuccess(this.message);
            this.is_processing = false;
            this.budgetDetail();
          } else {
            this.showError(this.message);
            this.is_processing = false;
          }
        },

        error: (e) => {
          console.error(e);
          this.message = 'Une erreur interne est survenue. Veuillez contacter l\'administrateur.';
          this.showError(this.message);
          this.is_processing = false;
        },

        complete: () => {

        }
      }
    )

  }

  confirmDeleteIncomeBudget(income_label: string, income_id: any) {
    Swal.fire({
      title: 'Confirmation !',
      text: 'Voulez-vous supprimer la source de revenu : ' + income_label + ' dans ce budget ?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
       //this.startLoader();
        this.income_service.delete({ 'id': income_id, 'budget_id': this.id }).subscribe({
          next: (res: any) => {
            this.message = res.message;
            if (res.status == 200) {
              this.showSuccess(this.message);
              this.listIncomes();
              //this.stopLoader();
            } else {
              this.showError(this.message);
              //this.stopLoader();
            }
          },
          error: ((e: any) => {
            console.error(e);
            this.message = 'Une erreur interne est survenue. Veuillez contacter le Groupe Scolar Plus.';
            this.showError(this.message);
            //this.stopLoader();
          })
        });
      }
    });
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }

  uploadInvoice(event: any) {
    this.global_event = event;
    this.invoice = event.target.files[0];
  }

  resetInputFile(){
    this.global_event.target.value = "";
  }

  confirmCloseBudget() {
    Swal.fire({
      title: 'Confirmation de clôture!',
      text: 'Etes-vous sûr de vouloir clôturer ce budget ?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
       //this.startLoader();
        this.budget_service.closeBudget({ 'budget_id': this.id, 'budget_repartitions': this.exp_budget }).subscribe({
          next: (res: any) => {
            this.message = res.message;
            if (res.status == 200) {
              this.showSuccess(this.message);
              this.listIncomes();
              //this.stopLoader();
            } else {
              this.showError(this.message);
              //this.stopLoader();
            }
          },
          error: ((e: any) => {
            console.error(e);
            this.message = 'Une erreur interne est survenue. Veuillez contacter le Groupe Scolar Plus.';
            this.showError(this.message);
            //this.stopLoader();
          })
        });
      }
    });
  }

}
