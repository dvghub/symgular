import {Component, OnInit} from '@angular/core';
import {CookieService} from 'ngx-cookie-service';
import {HttpClient} from '@angular/common/http';
import {Config} from '../config';

@Component({
  selector: 'app-leave',
  templateUrl: './leave.component.html',
  styleUrls: ['./leave.component.css']
})
export class LeaveComponent implements OnInit {
  config = new Config();
  today = new Date();
  user;
  date;
  predays;
  days;
  requests;
  stati;
  year;
  success = false;
  description = '';
  startTimeError = '';
  endTimeError = '';
  descriptionError = '';

  month = {
    number: 0,
    name: ''
  };
  hours = {
    total: 200,
    left: 0
  };

  constructor(private cookieService: CookieService, private http: HttpClient) {
    if (!cookieService.check('session')) {
      window.location.href = '/';
    } else {
      this.user = JSON.parse(cookieService.get('user'));
    }

    let m = (this.today.getMonth() + 1).toString();
    if (m.length < 2) {
      m = '0' + m;
    }
    let d = this.today.getDate().toString();
    if (d.length < 2) {
      d = '0' + d;
    }
    this.date = this.today.getFullYear() + '-' + m + '-' + d;

    this.month.number = this.today.getMonth() + 1;
    this.year = this.today.getFullYear();

    this.setMonth(this.month.number);

    http.get(this.config.url + 'requests/users/' + this.user.id).subscribe( data => {
      if ((data as any).success) {
        this.requests = (data as any).requests;
        this.hours.left = (data as any).hours;
      }
    });
  }

  static isLeapYear(year)  {
    return ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
  }

  ngOnInit() {}

  sendRequest(startDate, startTime, endDate, endTime, type, description) {
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
        this.http.post(this.config.url + 'request', {
          start_date: startDate,
          start_time: startTime,
          end_date: endDate,
          end_time: endTime,
          type,
          description,
          email: this.user.email
        }).pipe().subscribe( data => {
          if (!(data as any).success) {
            this.startTimeError = (data as any).startTimeError;
            this.endTimeError = (data as any).endTimeError;
            this.descriptionError = (data as any).descriptionError;
          } else {
            this.success = (data as any).success;
            if (type !== 'standard') {
              this.hours.left = (data as any).hours;
            }
            this.setMonth(this.month.number);
            this.description = '';
          }
        });
      }
    }
  }

  setMonth(n) {
    this.month.number = n;
    switch (n) {
      case 1:
        this.days = new Array(31);
        this.month.name = 'January';
        break;
      case 2:
        this.days = LeaveComponent.isLeapYear(this.year) ? new Array(29) : new Array(28);
        this.month.name = 'February';
        break;
      case 3:
        this.days = new Array(31);
        this.month.name = 'March';
        break;
      case 4:
        this.days = new Array(30);
        this.month.name = 'April';
        break;
      case 5:
        this.days = new Array(31);
        this.month.name = 'May';
        break;
      case 6:
        this.days = new Array(30);
        this.month.name = 'June';
        break;
      case 7:
        this.days = new Array(31);
        this.month.name = 'July';
        break;
      case 8:
        this.days = new Array(31);
        this.month.name = 'August';
        break;
      case 9:
        this.days = new Array(30);
        this.month.name = 'September';
        break;
      case 10:
        this.days = new Array(31);
        this.month.name = 'October';
        break;
      case 11:
        this.days = new Array(30);
        this.month.name = 'November';
        break;
      case 12:
        this.days = new Array(31);
        this.month.name = 'December';
        break;
    }

    this.http.get(
        this.config.url + 'requests/' + this.year + '/' + this.month.number + '/' + this.user.department + '/' + this.user.id
        ).subscribe( data => {
      this.stati = (data as any).days;
    });

    for (let i = 0; i < this.days.length; i ++) {
      this.days[i] = new Date(this.year, this.month.number - 1, i + 1);
    }

    this.predays = this.days[0].getDay() === 0 ? new Array(6) : new Array(this.days[0].getDay() - 1);
  }

  previousMonth() {
    if (this.month.number === 1) {
      this.month.number = 12;
      this.year -= 1;
    } else {
      this.month.number -= 1;
    }
    this.setMonth(this.month.number);
  }

  nextMonth() {
    if (this.month.number === 12) {
      this.month.number = 1;
      this.year += 1;
    } else {
      this.month.number += 1;
    }
    this.setMonth(this.month.number);
  }

  editRequest(id) {
    this.cookieService.set('id', id);
    window.location.href = 'edit';
  }

  deleteRequest(id) {
    this.http.delete(this.config.url + 'notices/' + id).subscribe( data => {
      if ((data as any).success) {
        this.requests.forEach( (item, index) => {
          if (this.requests[index].id === id) {
            this.requests.splice(index, 1);
          }
        });
      }
    });
  }
}
