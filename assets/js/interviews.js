"use strict";

(function ($) {
    $(document).ready(function () {
        Vue.component('v-select', VueSelect.VueSelect);
        new Vue({
            el: '#stm_lms_interviews',
            data: function data() {
                return {
                    interviews: [],
                    showForm: false,
                    showList: true,
                    loading: true,
                    total: false,
                    userData: {},
                    form: {
                        course: '',
                        student: '',
                        title: '',
                        date: '',
                        vote: '',
                        status: '',
                        comment: '',
                        postId: null
                    },
                    students: [],
                    courses: [],
                    votes: [
                        '1',
                        '2',
                        '3',
                        '4',
                        '5',
                        '6',
                        '7',
                        '8',
                        '9',
                        '10'
                    ],
                    showCurrent: false,
                    notPassedCount: 0,
                    passedCount: 0,
                    pendingCount: 0,
                    currentInterview: {}
                };
            },
            beforeMount: function beforeMount() {
                this.interviews = this.interviews.sort((x,y) => y.status - x.status);
            },
            mounted: function mounted() {
                this.setUserData();
            },
            methods: {
                setUserData: function setUserData() {
                    this.userData = userData
                    this.getInterviews()
                    this.getCoursesUsers()
                },
                generateClass: function generateClass(e) {
                    switch (e) {
                        case true:
                            return 'fa-check';
                        case false:
                            return 'fa-times';
                        case 'pending':
                            return 'fa-clock';
                        default:
                            return 'fa-clock';
                    }
                },
                interviewsSort: function interviewsSort(arr) {
                    const sort = $(event.target).val()

                    switch (sort) {
                        case 'status_nonpassed':
                            arr.sort(
                                function (x,y) {
                                    if(x.status === 'pending') x.status = 2
                                    if( y.status === 'pending') y.status = 2
                                    if (x.status > y.status) {
                                        return 1;
                                    }
                                    if (x.status < y.status) {
                                        return -1;
                                    }

                                    return 0;
                                }
                            );
                            this.interviews = arr.map((element) => {
                                if(element.status === 2) {
                                    element.status = 'pending'
                                }
                                return element
                            });
                            break;
                        case 'status_passed':
                            arr.sort(
                                function (x,y) {
                                    if(x.status === 'pending') x.status = -2
                                    if( y.status === 'pending') y.status = -2
                                    if (x.status > y.status) {
                                        return -1;
                                    }
                                    if (x.status < y.status) {
                                        return 1;
                                    }

                                    return 0;
                                }
                            );
                            this.interviews = arr.map((element) => {
                                if(element.status === -2) {
                                    element.status = 'pending'
                                }
                                return element
                            });
                            break;
                        case 'status_pending':
                            arr.sort(
                                function (x,y) {
                                    if(x.status === 'pending') x.status = 2
                                    if( y.status === 'pending') y.status = 2
                                    if (x.status > y.status) {
                                        return -1;
                                    }
                                    if (x.status < y.status) {
                                        return 1;
                                    }

                                    return 0;
                                }
                            );
                            this.interviews = arr.map((element) => {
                                if(element.status === 2) {
                                    element.status = 'pending'
                                }
                                return element
                            });

                            break;
                    }
                },
                getInterviews: function getInterviews() {
                    const vm = this;

                    const data = 'action=stm_lms_get_interviews&nonce=' + this.userData.nonce + '&userId=' + this.userData.userId + '&isInstructor=' + this.userData.isInstructor;
                    vm.loading = true;
                    $.ajax({
                        url: this.userData.ajaxUrl,
                        type: 'POST',
                        data: data,
                        success: function( data ) {
                            vm.interviews = data
                            vm.statusLengths(vm.interviews)
                            vm.loading = false;
                        }
                    });
                },
                createInterview: function createInterview() {
                    this.showForm = !this.showForm
                    this.showList = false
                },
                addInterview: function addInterview() {
                    const vm = this
                    vm.loading = true;
                    $('#stm_lms_interviews .stm-lms-message').hide()
                    if(vm.form.course === "" || vm.form.student === "" || vm.form.title === "" || vm.form.date === "") {
                        $('#stm_lms_interviews .stm-lms-message').show().addClass('error').text('Please fill required fields')
                        vm.loading = false;
                        return
                    }

                    const data = 'action=stm_add_interview&nonce=' + this.userData.nonce + '&userId=' + this.userData.userId + '&data=' + JSON.stringify(vm.form);
                    $.ajax({
                        url: this.userData.ajaxUrl,
                        type: 'POST',
                        data: data,
                        success: function( data ) {
                            if(data) {
                                $('#stm_lms_interviews .stm-lms-message').show().addClass(data.status).text(data.message)
                            }
                            vm.loading = false;
                            window.setTimeout(function(){location.reload()},1000)
                        }
                    });
                },
                cancelCreateInterview: function cancelCreateInterview() {
                    this.showForm = !this.showForm
                    this.showList = true
                    this.form = {course: '', student: '', title: '', date: '', vote: '', comment: '', postId: null}
                },
                openInterview: function openInterview(id) {
                    const vm = this;
                    if(id) {
                        vm.interviews.forEach(function (item) {
                            if (item.id === id) {
                                vm.currentInterview = item
                            }
                        })
                    }
                    vm.showCurrent = !vm.showCurrent
                    vm.showList =  !vm.showList
                },
                statusLengths: function statusLengths(interviews) {
                    const vm = this;
                    interviews.forEach(function (item) {
                        if(item.status === true) {
                            vm.passedCount++
                        } else if (item.status === false) {
                            vm.notPassedCount++
                        }
                    });
                },
                editInterview: function editInterview(id) {
                    const vm = this
                    if(id) {
                        vm.interviews.forEach(function (item) {
                            if (item.id === id) {
                                vm.form.course = item.course
                                vm.form.student = item.student
                                vm.form.title = item.title
                                vm.form.date = new Date(item.date)
                                vm.form.status = item.status
                                vm.form.vote = item.vote
                                vm.form.comment = item.comment
                                vm.form.postId = id
                            }
                        })
                        vm.students.forEach(function (item) {
                            if(vm.form.student === item.label) {
                                vm.form.student = item
                            }
                        })
                        vm.courses.forEach(function (item) {
                            if(vm.form.course === item.label) {
                                vm.form.course = item
                            }
                        })

                        vm.createInterview()
                        vm.showCurrent = !vm.showCurrent
                    }
                },
                getCoursesUsers: function getCourses() {
                    const vm = this;

                    const data = 'action=stm_get_courses_users_interview&nonce=' + this.userData.nonce;
                    $.ajax({
                        url: this.userData.ajaxUrl,
                        type: 'POST',
                        data: data,
                        success: function( data ) {
                            if(data.courses.length !== 0) {
                                vm.setUsersCourses(data.courses, vm.courses)
                            }
                            if(data.students.length !== 0) {
                                vm.setUsersCourses(data.students, vm.students)
                            }
                        }
                    });
                },
                setUsersCourses: function setUsersCourses(data, name) {
                    for (const key in data) {
                        if (data.hasOwnProperty(key)) {
                            name.push({ label: data[key], code: key})
                        }
                    }
                },
            }
        });
    });
})(jQuery);