String.prototype.capitalizeFirstLetter = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

function getRunningAndWaitingJobs() {
    $.getJSON("/queue/jobs.json?status=waiting|running", function (jobs) {
        var newJobsHtml = "";
        jobs.forEach(function (job) {
            
            if (job.state == "waiting") {
                labelClass = "label-info"
            } else {
                labelClass = "label-warning";
            }
            
            jobDate = job.addedAt.date + " " + job.addedAt.timezone;
            newJobsHtml += "<p>";
            newJobsHtml += "<span class=\"label " + labelClass + "\">" + job.state.capitalizeFirstLetter() + "</span>";
            newJobsHtml += " <span>" + job.username + "</span>";
            newJobsHtml += " <span>" + $.format.date(job.addedAt.date, "MMMM d yyyy H:mm")+ "</span>";
            newJobsHtml += "</p>";
        });
        //July 13, 2015 15:07
        $("#jobs").html(newJobsHtml);
    });
}

$(document).ready(function (){
    getRunningAndWaitingJobs();
    var tid = setInterval(getRunningAndWaitingJobs, 500);
});
