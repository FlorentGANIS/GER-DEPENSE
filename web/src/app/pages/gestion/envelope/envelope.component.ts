import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { EnvelopeService } from 'src/app/shared/services/envelope.service';

@Component({
  selector: 'app-envelope',
  standalone: false,
  templateUrl: './envelope.component.html',
  styleUrl: './envelope.component.scss'
})
export class EnvelopeComponent implements OnInit{
  breadCrumbItems!: Array<{}>; envelopes: any[] = []; msg: string = '';  today!: Date; is_processing: boolean = false;

  p: number = 1;

  constructor(private envelope_service: EnvelopeService, 
    private ngxLoader: NgxUiLoaderService, private toastr: ToastrService,) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Mes enveloppes', active: true }
    ];

    this.today = new Date();

   
    this.listEnvelopes();

  }

  listEnvelopes() {
    this.ngxLoader.startLoader('loader-spin');
    this.envelope_service.list().subscribe(
      {
        next: (v: any) => {
          if(v.status == 200){
            this.envelopes = v.data;
            this.ngxLoader.stopLoader('loader-spin');
          }          
        },

        error: (e) => {
          console.error(e);
          this.ngxLoader.stopLoader('loader-spin');
        },

        complete: () => {
        }
      }
    );
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }
}
