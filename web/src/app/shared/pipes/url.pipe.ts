import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Pipe({
  name: 'url',
  standalone: false
})
export class UrlPipe implements PipeTransform {

  constructor(private sanitizer: DomSanitizer) { }
  transform(url: any): unknown {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  }

}
