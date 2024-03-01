import { ComponentFixture, TestBed } from '@angular/core/testing';

import { HistoryEnvelopeComponent } from './history-envelope.component';

describe('HistoryEnvelopeComponent', () => {
  let component: HistoryEnvelopeComponent;
  let fixture: ComponentFixture<HistoryEnvelopeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HistoryEnvelopeComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(HistoryEnvelopeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
