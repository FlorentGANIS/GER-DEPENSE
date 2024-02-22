import { ComponentFixture, TestBed } from '@angular/core/testing';

import { HorizontaltopbarComponent } from './horizontaltopbar.component';
import { TranslateService, TranslateStore } from '@ngx-translate/core';
import { HttpClientTestingModule } from '@angular/common/http/testing';

describe('HorizontaltopbarComponent', () => {
  let component: HorizontaltopbarComponent;
  let fixture: ComponentFixture<HorizontaltopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [HorizontaltopbarComponent],
      providers: [TranslateService, TranslateStore],
      imports: [HttpClientTestingModule],
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(HorizontaltopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
