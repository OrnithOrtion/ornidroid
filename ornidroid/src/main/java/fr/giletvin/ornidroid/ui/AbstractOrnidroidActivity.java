package fr.giletvin.ornidroid.ui;

import android.app.Activity;
import android.content.Intent;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import fr.giletvin.ornidroid.R;

/**
 * The Class AbstractOrnidroidActivity. Extending this class enables the menu.
 */
public abstract class AbstractOrnidroidActivity extends Activity {
	/*
	 * (non-Javadoc)
	 * 
	 * @see android.app.Activity#onCreateOptionsMenu(android.view.Menu)
	 */
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		MenuInflater inflater = getMenuInflater();
		inflater.inflate(R.menu.options_menu, menu);
		return true;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see android.app.Activity#onOptionsItemSelected(android.view.MenuItem)
	 */
	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		case R.id.search:
			onSearchRequested();
			return true;
		case R.id.preferences:
			startActivity(new Intent(this, OrnidroidPreferenceActivity.class));
			return (true);
		case R.id.about:
			startActivity(new Intent(this, AboutActivity.class));
			return (true);
		default:
			return super.onOptionsItemSelected(item);
		}
	}
}
