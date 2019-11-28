import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'app-leave',
  templateUrl: './leave.component.html',
  styleUrls: ['./leave.component.css']
})
export class LeaveComponent implements OnInit {
  @Input()
  admin;

  constructor() { }

  ngOnInit() {
  }

}
