import { Component, OnInit } from '@angular/core';
import {CookieService} from 'ngx-cookie-service';
import {HttpClient} from '@angular/common/http';
import {Config} from '../config';

@Component({
  selector: 'app-leave-edit',
  templateUrl: './leave-edit.component.html',
  styleUrls: ['./leave-edit.component.css']
})
export class LeaveEditComponent implements OnInit {
  config = new Config();
  id;
  user;
  request;
  success = false;
  startTimeError = '';
  endTimeError = '';
  descriptionError = '';

  constructor(private cookieService: CookieService, private http: HttpClient) {
    if (cookieService.check('id')) {
      this.id = this.cookieService.get('id');
    } else {
      window.location.href = '';
    }
    if (cookieService.check('user')) {
      this.user = JSON.parse(cookieService.get('user'));
    } else {
      window.location.href = '';
    }

    http.get(this.config.url + 'requests/' + this.id).subscribe( data => {
      this.request = (data as any);
    });
  }

  ngOnInit() {}

  sendRequest(startDate, startTime, endDate, endTime, description) {
    this.success = false;
    this.startTimeError = '';
    this.endTimeError = '';
    this.descriptionError = '';

    if (startTime.split(':')[1] !== '00' &&
      startTime.split(':')[1] !== '30') {
      this.startTimeError = 'Time should be on the hour or half hour.';
    } else {
      if (endTime.split(':')[1] !== '00' &&
        endTime.split(':')[1] !== '30') {
        this.endTimeError = 'Time should be on the hour or half hour.';
      } else {
        this.http.patch(this.config.url + 'requests/' + this.id, {
          start_date: startDate,
          start_time: startTime,
          end_date: endDate,
          end_time: endTime,
          description,
          email: this.user.email
        }).subscribe( data => {
          this.success = (data as any).success;
          if (!this.success) {
            this.startTimeError = (data as any).startTimeError;
            this.endTimeError = (data as any).endTimeError;
            this.descriptionError = (data as any).descriptionError;
          } else {
            window.location.href = 'leave';
          }
        });
      }
    }
  }
}
