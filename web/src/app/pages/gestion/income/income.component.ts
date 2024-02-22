import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { IncomeService } from 'src/app/shared/services/income.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-income',
  standalone: false,
  templateUrl: './income.component.html',
  styleUrl: './income.component.scss'
})
export class IncomeComponent {
  breadCrumbItems!: Array<{}>; incomes: any[] = []; msg: string = ''; incomeForm!: FormGroup;

  is_processing: boolean = false; p: number = 1; successMessage = ''; display: boolean = false; to_edit: boolean = false;

  constructor(private fb: FormBuilder, private income_service: IncomeService, private modalService: NgbModal,
     private toastr: ToastrService, private ngxLoader: NgxUiLoaderService) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Sources de revenus', active: true }
    ];
    this.incomeForm = this.fb.group({
      id: [''],
      label: ['', [Validators.required, Validators.minLength(4), Validators.maxLength(50)]]
    });

    this.getIncomes();
  }

  getIncomes() {
    this.startLoader();
    this.income_service.list().subscribe({
      next: (v: any) => {
        this.incomes = v.data;
        this.stopLoader();

      },
      error: (e: any) => {
        console.error(e);
        this.msg = "Erreur";
      }
    });
  }

  startLoader() {
    this.ngxLoader.startLoader('loader-spin');
  }

  stopLoader() {
    this.ngxLoader.stopLoader('loader-spin');
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }

  saveIncome() {
    this.is_processing = true;
    this.income_service.create(this.incomeForm.value).subscribe({
      next: (v: any) => {
        this.msg = v.message;
        if (v.status == 200) {
          this.showSuccess(this.msg);
          this.incomeForm.reset();
          if (this.incomeForm.value.id !== null || this.incomeForm.value.id !== '') {
            this.modalService.dismissAll();
          }
          this.getIncomes();
          this.is_processing = false;

        } else {
          this.showError(this.msg);
          this.is_processing = false;
        }
      },
      error: (e: any) => {
        console.error(e);
        this.msg = 'Une erreur interne est survenue. Veuillez contacter le Groupe Scolar Plus.';
        this.showError(this.msg);
      }
    });
  }


  get form() {
    return this.incomeForm.controls;
  }

  resetForm() {
    this.incomeForm.reset();
    this.modalService.dismissAll();
  }

  /**
   * Open modal
   * @param content modal content
   */
  openModal(content: any, for_editing: boolean, income: any = null) {
    this.to_edit = for_editing;
    if (income != null) {
      this.incomeForm.patchValue({
        id: income?.id,
        label: income?.label
      });
    }
    this.display = true;
    this.modalService.open(content, { centered: true });
  }



  confirm(income: any) {
    Swal.fire({
      title: 'Confirmation !',
      text: 'Voulez-vous supprimer la source de revenu : ' + income?.label + ' ?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
        this.startLoader();
        this.income_service.delete({ id: income?.id }).subscribe({
          next: (res: any) => {
            this.msg = res.message;
            if (res.status == 200) {
              this.showSuccess(this.msg);
              this.getIncomes();
              this.stopLoader();
            } else {
              this.showError(this.msg);
              this.stopLoader();
            }
          },
          error: ((e: any) => {
            console.error(e);
            this.msg = 'Une erreur interne est survenue. Veuillez contacter le Groupe Scolar Plus.';
            this.showError(this.msg);
            this.stopLoader();
          })
        });
      }
    });
  }

}
