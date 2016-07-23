#! /usr/bin/python

from Tkinter import *
import json
import os.path
import subprocess

class TSPViewer(Frame):
	locations = None

	def __init__(self, parent):
		Frame.__init__(self, parent)
		self.parent = parent
		self.initUI()

	def initUI(self):
		self.parent.title("TSP Viewer")
		self.pack(fill = BOTH, expand = 1)

		canvas = Canvas(self)

		if (not os.path.isfile('location_data.json')):
			print 'Could not find location data!\n'
			quit()
		else:
			# Load locations
			location_data_json_file = open('location_data.json', 'r')
			location_data_json_string = location_data_json_file.read()
			location_data_json_file.close()
			location_data = json.loads(location_data_json_string)
			self.locations = location_data['locations']

			# Draw locations
			for location in self.locations:
				x = location['x'];
				y = location['y'];
				canvas.create_oval(x, y, x + 6, y + 6, outline = "black", fill = "black")

			# Load best route
			if (not os.path.isfile('route.txt')):
				print 'Could not find route data!\n'
				quit()
			else:
				route_data_file = open('route.txt', 'r')

				# Draw best route
				# Get initial location to begin
				initial_id = int(route_data_file.readline().rstrip('\r\n'))
				from_id = initial_id
				# Get each location from file
				for line in route_data_file:
					to_id = int(line.rstrip('\r\n'))
					# Skip the first location since we already got it
					if (from_id != to_id):
						canvas.create_line(self.getLocation(from_id)['x'] + 3, self.getLocation(from_id)['y'] + 3, self.getLocation(to_id)['x'] + 3, self.getLocation(to_id)['y'] + 3)
					from_id = to_id

				# Draw path from last location to first location
				canvas.create_line(self.getLocation(from_id)['x'] + 3, self.getLocation(from_id)['y'] + 3, self.getLocation(initial_id)['x'] + 3, self.getLocation(initial_id)['y'] + 3)

		canvas.pack(fill = BOTH, expand = 1)

	# Get a location from locations with the given id
	def getLocation(self, id):
		for location in self.locations:
			if (location["id"] == id):
				return location
		return None

# Check to see if PHP is accessible from the shell
def hasPHP():
	try:
		subprocess.check_call(['php', '-v'], stdout = subprocess.PIPE)
		return True
	except:
		return False

def main():
	# Run TSP.php if neccessary data is not present. Quit on error.
	if (not os.path.isfile('location_data.json') or not os.path.isfile('route.txt')):
		print 'No location data found. Attempting to generate location data.'
		if (not hasPHP()):
			print 'You do not have PHP installed. It needs to be installed to continue.'
			quit()
		elif (not os.path.isfile('TSP.php')):
			print 'TSP.php not found. Cannot continue.'
			quit()

		if (subprocess.call(['php', 'TSP.php']) != 0):
			print 'There was an error during the generation of location data.'
			quit()

	# Create UI
	root = Tk()
	tsp_viewer = TSPViewer(root)
	root.geometry("520x520")
	root.mainloop()

if __name__ == '__main__':
	main()
