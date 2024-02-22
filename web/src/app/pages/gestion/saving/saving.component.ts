import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { CategoryService } from 'src/app/shared/services/category.service';
import Swal from 'sweetalert2';
@Component({
  selector: 'app-saving',
  standalone: false,
  templateUrl: './saving.component.html',
  styleUrl: './saving.component.scss'
})
export class SavingComponent {
  breadCrumbItems!: Array<{}>; savings: any[] = []; msg: string = ''; saving_form!: FormGroup;

  is_processing: boolean = false; p: number = 1; message = ''; display: boolean = false; to_edit: boolean = false;

  constructor(private fb: FormBuilder, private category_service: CategoryService, private modalService: NgbModal,
     private toastr: ToastrService, private ngxLoader: NgxUiLoaderService) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Epargnes', active: true }
    ];
    this.saving_form = this.fb.group({
      id: [''],
      label: ['', [Validators.required, Validators.minLength(4), Validators.maxLength(50)]]
    });

    this.listSavings();
  }

  listSavings() {
    this.startLoader();
    this.category_service.savingChargeList().subscribe({
      next: (v: any) => {
        this.savings = v.data;
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

  saveSaving() {
    this.is_processing = true;
    this.category_service.create({
      'id': this.saving_form.get('id')?.value,
      'designation': this.saving_form.get('label')?.value,
      'type' : 'EPARGNE'
    }).subscribe({
      next: (v: any) => {
        this.msg = v.message;
        if (v.status == 200) {
          this.showSuccess(this.msg);
          this.saving_form.reset();
          if (this.saving_form.value.id !== null || this.saving_form.value.id !== '') {
            this.modalService.dismissAll();
          }
          this.listSavings();
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
    return this.saving_form.controls;
  }

  resetForm() {
    this.saving_form.reset();
    this.modalService.dismissAll();
  }

  /**
   * Open modal
   * @param content modal content
   */
  openModal(content: any, for_editing: boolean, saving: any = null) {
    this.to_edit = for_editing;
    if (saving != null) {
      this.saving_form.patchValue({
        id: saving?.id,
        label: saving?.designation
      });
    }
    this.display = true;
    this.modalService.open(content, { centered: true });
  }



  confirm(saving: any) {
    Swal.fire({
      title: 'Confirmation !',
      text: 'Voulez-vous supprimer l\'épargne : ' + saving?.label + ' ?',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#34c38f',
      cancelButtonColor: '#f46a6a',
      confirmButtonText: 'Oui'
    }).then(result => {
      if (result.value) {
        this.startLoader();
        this.category_service.delete({ id: saving?.id }).subscribe({
          next: (res: any) => {
            this.msg = res.message;
            if (res.status == 200) {
              this.showSuccess(this.msg);
              this.listSavings();
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

  confirmChangeStatusSaving(saving: any) {
    let testResponse = '';
    if (saving?.status == 1) {
      testResponse = 'Êtes-vous sûr de vouloir désactiver l\'épargne '+ saving?.label +' ?';
    }
    if (saving?.status == 0) {
      testResponse = 'Êtes-vous sûr de vouloir activer l\'épargne '+ saving?.label +' ?';
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
        this.changeSavingStatus(saving?.id);
      }
    });
    
  }

  changeSavingStatus(saving_id: any) {
    this.is_processing = true;
    this.category_service.changeCategoryStatus({ 'id': saving_id }).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.showSuccess(this.message);
            this.listSavings();
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
