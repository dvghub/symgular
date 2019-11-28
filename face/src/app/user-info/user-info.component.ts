import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-user-info',
  templateUrl: './user-info.component.html',
  styleUrls: ['./user-info.component.css']
})

export class UserInfoComponent implements OnInit {
  employees = {};
  user;
  current = {
    email: '',
    name : '',
    department: '',
    admin: false
  };
  selected;
  uploadMessage = '';
  oldPasswordError = '';
  passwordError = '';

  constructor(private http: HttpClient, private cookieService: CookieService) {
    this.http.get('http://localhost:8000/users').subscribe( data => {
      this.employees = (data as any).employees;
    });
    if (cookieService.check('session')) {
      this.user = JSON.parse(cookieService.get('user'));
      this.load(this.user.email);
    }
  }

  ngOnInit() {}

  load(email) {
    this.http.post('http://localhost:8000/email', {email}).pipe().subscribe( data => {
      this.current.email = (data as any).email;
      this.current.name = (data as any).name;
      this.current.department = (data as any).department;
      this.current.admin = (data as any).admin === 1;
      this.selected = this.current.email;
    });
  }

  update() {
    const passwordOld = document.getElementById('password_old') !== null ? document.getElementById('password_old').valueOf().value : '';
    const password = document.getElementById('password') !== null ? document.getElementById('password').valueOf().value : '';
    const passwordRepeat = document.getElementById('password_repeat') !== null ?
            document.getElementById('password_repeat').valueOf().value : '';
    const department = document.getElementById('department') !== null ?
            document.getElementById('department').valueOf().selectedOptions[0].value : this.current.department;
    const admin = document.getElementById('admin') !== null ? document.getElementById('admin').checked : false;

    this.http.post('http://localhost:8000/update', {
      editor_admin: this.user.admin,
      email: this.current.email,
      password_old: passwordOld,
      password,
      password_repeat: passwordRepeat,
      department,
      admin
    }).pipe().subscribe( data => {
      if ((data as any).response) {
        this.uploadMessage = 'Update succeeded';
        console.log('succeeded');
      } else {
        if ((data as any).old_password_error) {
          this.oldPasswordError = (data as any).old_password_error;
        }
        if ((data as any).password_error) {
          this.passwordError = (data as any).password_error;
        }
      }
    });
  }
}
