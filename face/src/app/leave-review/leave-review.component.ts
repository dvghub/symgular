import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-leave-review',
  templateUrl: './leave-review.component.html',
  styleUrls: ['./leave-review.component.css']
})
export class LeaveReviewComponent implements OnInit {
  config = new Config();
  user;
  requests;
  selected;
  current;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (cookieService.check('user')) {
      this.user = JSON.parse(this.cookieService.get('user'));
      if (!this.user.admin && this.user.department !== 'hr') {
        window.location.href = '/';
      }
    } else {
      window.location.href = '/';
    }
    this.getRequests();
  }

  ngOnInit() {}

  getRequests() {
    this.http.get(this.config.url + 'requests/unapproved').subscribe( data => {
      this.requests = (data as any).requests;
      if (this.requests.length !== 0) {
          this.load(this.requests[0].id);
      } else {
        this.current = undefined;
      }
    });
  }

  load(id) {
    this.selected = id;
    this.requests.forEach( (item, index) => {
      if (this.requests[index].id === id) {
        this.current = this.requests[index];
      }
    });
  }

  approve(id) {
    this.http.patch(this.config.url + 'requests/' + id + '/approve', {}).subscribe( data => {
      if ((data as any).success) {
        this.getRequests();
      }
    });
  }

  deny(id) {
    this.http.delete(this.config.url + 'requests/' + id).subscribe( data => {
      if ((data as any).success) {
        this.getRequests();
      }
    });
  }
}
