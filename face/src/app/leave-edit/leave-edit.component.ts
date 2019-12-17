import { Component, OnInit } from '@angular/core';
import {CookieService} from 'ngx-cookie-service';
import {HttpClient} from '@angular/common/http';

@Component({
  selector: 'app-leave-edit',
  templateUrl: './leave-edit.component.html',
  styleUrls: ['./leave-edit.component.css']
})
export class LeaveEditComponent implements OnInit {
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

    http.post('http://localhost:8000/idrequest', {id: this.id}).pipe().subscribe( data => {
      this.request = (data as any);
      console.log(this.request);
    });
  }

  ngOnInit() {}

  sendRequest(startDate, startTime, endDate, endTime, description) {
    this.success = false;
    this.startTimeError = '';
    this.endTimeError = '';
    this.descriptionError = '';

    this.http.post('http://localhost:8000/editrequest', {
      start_date: startDate,
      start_time: startTime,
      end_date: endDate,
      end_time: endTime,
      description,
      email: this.user.email,
      id: this.request.id
    }).pipe().subscribe( data => {
      console.log(JSON.stringify(data));
      this.success = (data as any).success;
      if (!this.success) {
        this.startTimeError = (data as any).start_time_error;
        this.endTimeError = (data as any).end_time_error;
        this.descriptionError = (data as any).description_error;
      } else {
        window.location.href = 'leave';
      }
    });
  }
}
