import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SidebarComponent } from './sidebar.component';
import { MissingTranslationHandler, TranslateCompiler, TranslateLoader, TranslateModule, TranslateParser, TranslateService, TranslateStore } from '@ngx-translate/core';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { Observable, of } from 'rxjs';

class MockTranslateLoader implements TranslateLoader {
  getTranslation(lang: string): Observable<any> {
    // Provide a mock translation object for testing
    const translations = {
      // Your translations here
    };
    return of(translations);
  }
}

describe('SidebarComponent', () => {
  let component: SidebarComponent;
  let fixture: ComponentFixture<SidebarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [SidebarComponent],
      providers: [TranslateService, TranslateStore,TranslateLoader,TranslateCompiler,TranslateParser,MissingTranslationHandler,],
      imports: [HttpClientTestingModule,
        TranslateModule.forRoot({
          useDefaultLang: true, // This is the configuration that requires InjectionToken USE_DEFAULT_LANG
          loader: { provide: TranslateLoader, useClass: MockTranslateLoader },
        }),],
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(SidebarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
