import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { EnvelopeService } from 'src/app/shared/services/envelope.service';

@Component({
  selector: 'app-history-envelope',
  standalone: false,
  templateUrl: './history-envelope.component.html',
  styleUrl: './history-envelope.component.scss'
})
export class HistoryEnvelopeComponent implements OnInit{

  breadCrumbItems!: Array<{}>; histories: any[] = []; msg: string = '';  today!: Date; is_processing: boolean = false;

  p: number = 1;

  constructor(private envelope_service: EnvelopeService, 
    private ngxLoader: NgxUiLoaderService, private toastr: ToastrService,) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Mouvement des enveloppes', active: true }
    ];

    this.today = new Date();

   
    this.getHistory();

  }

  getHistory() {
    this.ngxLoader.startLoader('loader-spin');
    this.envelope_service.historyEnvelopes({}).subscribe(
      {
        next: (v: any) => {
          if(v.status == 200){
            this.histories = v.data;
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
