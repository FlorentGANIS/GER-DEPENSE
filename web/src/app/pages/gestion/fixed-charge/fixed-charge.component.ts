import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { CategoryService } from 'src/app/shared/services/category.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-fixed-charge',
  standalone: false,
  templateUrl: './fixed-charge.component.html',
  styleUrl: './fixed-charge.component.scss'
})
export class FixedChargeComponent {
  breadCrumbItems!: Array<{}>; fixed_charges: any[] = []; msg: string = ''; charge_form!: FormGroup;

  is_processing: boolean = false; p: number = 1; message = ''; display: boolean = false; to_edit: boolean = false;

  constructor(private fb: FormBuilder, private category_service: CategoryService, private modalService: NgbModal,
     private toastr: ToastrService, private ngxLoader: NgxUiLoaderService) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Charges fixes', active: true }
    ];
    this.charge_form = this.fb.group({
      id: [null],
      designation: ['', [Validators.required, Validators.minLength(4), Validators.maxLength(50)]],
      fixed_amount: [null, [Validators.required]]
    });

    this.listFixedCharge();
  }

  listFixedCharge() {
    this.startLoader();
    this.category_service.fixedChargeList().subscribe({
      next: (v: any) => {
        this.fixed_charges = v.data;
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

  saveFixedCharge() {
    this.is_processing = true;
    this.category_service.create({
      'id': this.charge_form.get('id')?.value,
      'designation': this.charge_form.get('designation')?.value,
      'type' : 'FIXE',
      'fixed_amount': this.charge_form.get('fixed_amount')?.value
    }).subscribe({
      next: (v: any) => {
        this.msg = v.message;
        if (v.status == 200) {
          this.showSuccess(this.msg);
          this.charge_form.reset();
          if (this.charge_form.value.id !== null || this.charge_form.value.id !== '') {
            this.modalService.dismissAll();
          }
          this.listFixedCharge();
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
    return this.charge_form.controls;
  }

  resetForm() {
    this.charge_form.reset();
    this.modalService.dismissAll();
  }

  /**
   * Open modal
   * @param content modal content
   */
  openModal(content: any, for_editing: boolean, charge: any = null) {
    this.to_edit = for_editing;
    if (charge != null) {
      this.charge_form.patchValue({
        id: charge?.id,
        designation: charge?.designation,
        fixed_amount: charge?.fixed_amount
      });
    }
    this.display = true;
    this.modalService.open(content, { centered: true });
  }



  confirm(cv: any) {
    Swal.fire({
      title: 'Confirmation !',
      text: 'Voulez-vous supprimer la charge fixe : ' + cv?.designation + ' ?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
        this.startLoader();
        this.category_service.delete({ id: cv?.id }).subscribe({
          next: (res: any) => {
            this.msg = res.message;
            if (res.status == 200) {
              this.showSuccess(this.msg);
              this.listFixedCharge();
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

  confirmChangeStatusIncome(charge: any) {
    let testResponse = '';
    if (charge?.status == 1) {
      testResponse = 'Êtes-vous sûr de vouloir désactiver la charge fixe '+ charge?.designation +' ?';
    }
    if (charge?.status == 0) {
      testResponse = 'Êtes-vous sûr de vouloir activer la charge fixe '+ charge?.designation +' ?';
    }
    Swal.fire({
      title: 'Confirmation !',
      text: testResponse,
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
        this.startLoader();
        this.changeFixeChargeStatus(charge);
      }
    });
    
  }

  changeFixeChargeStatus(charge: any) {
    this.is_processing = true;
    this.category_service.changeCategoryStatus({ 'id': charge?.id }).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.showSuccess(this.message);
            this.listFixedCharge();
            this.is_processing = false;
          } else {
            this.showError(this.message);
            this.is_processing = false;
          }

        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }

      });
  }
}
