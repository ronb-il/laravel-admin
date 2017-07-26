<div class="row">
    <div class="col-lg-3">
        <label for="selected-daterange">Date Filter:</label>
        <div class="input-group">
            <input type="text" name="daterange" class="form-control" id="selected-daterange" placeholder="Date" />
            <span class="input-group-btn">
                <button onclick="dateButtonClicked()" class="btn btn-default" type="button">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                </button>
            </span>

        </div>
    </div>

    <div class="col-lg-3">
        <label>User Name Filter:</label>
        <input type="text" name="userName" class="form-control" placeholder="Username" />
    </div>

    <div class="col-lg-6">
        <div class="pull-right">
            <label>&nbsp;</label>
            <ul class="list-inline">
                <li>show</li>
                <li>
                    <select id="entries-per-page" class="form-control input-sm" style="display: inline;">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </li>
                <li>entries</li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <table id="logs" class="table table-striped" initialized="false">
        <thead>
            <tr>
                <th>Date</th>
                <th>User Name</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
