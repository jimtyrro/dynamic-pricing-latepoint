<div class="date-picker-form-w">
	<div class="latepoint-lightbox-content">
		<div class="custom-day-schedule-w date-picker-schedule-w">
			<div class="custom-day-calendar" data-period-type="single" data-picking="start">
				<div class="custom-day-settings-w">
					<?php echo OsFormHelper::hidden_field( 'input_field_id', $input_field_id ); ?>
					<div class="start-day-input-w">
						<?php echo OsFormHelper::hidden_field( 'date', $date->format( 'Y-m-d' ), [ 'id' => 'start_custom_date' ] ); ?>
					</div>
				</div>
				<div class="custom-day-calendar-head">
					<h3 class="calendar-heading"><?php esc_html_e( 'Pick a Date', 'latepoint' ); ?></h3>
					<?php echo OsFormHelper::select_field( 'custom_day_calendar_month', false, OsUtilHelper::get_months_for_select(), $date->format( 'n' ) ); ?>
					<?php
					if ( ! empty( $earliest_year ) ) {
						$startYear = intval( $earliest_year );
					} else {
						$startYear = intval( date( 'Y' ) ) - 100;
					}

					if ( ! empty( $latest_year ) ) {
						$endYear = intval( $latest_year );
					} else {
						$endYear = $startYear + 100;
					}

					$selectableYears = [];
					for ( $year = $startYear; $year <= $endYear; $year ++ ) {
						$selectableYears[] = $year;
					}

					echo OsFormHelper::select_field( 'custom_day_calendar_year', false, $selectableYears, $date->format( 'Y' ) ); ?>
				</div>
				<div class="custom-day-calendar-month"
				     data-route="<?php echo OsRouterHelper::build_route_name( 'calendars', 'load_monthly_calendar_days_only' ); ?>">
					<?php OsCalendarHelper::generate_monthly_calendar_days_only( $date->format( 'Y-m-d' ), true ); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="latepoint-lightbox-footer" <?php if ( ! $date_is_preselected ) {
		echo 'style="display: none;"';
	} ?>>
		<a href="#"
		   class="latepoint-btn latepoint-btn-block latepoint-btn-lg latepoint-btn-outline tx-date-picker-save-btn"><?php esc_html_e( 'Save', 'latepoint' ); ?></a>
	</div>
</div>