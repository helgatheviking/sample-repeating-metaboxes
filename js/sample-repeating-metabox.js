/* js/admin.js */
var Repeating_Meta = Repeating_Meta || {};

(function($, Repeating_Meta) {

    // Model
    Repeating_Meta.Section = Backbone.Model.extend({
        defaults: {
            'input': '',
            'select': 'alpha',
            'textarea': '',
            'state': 'closed'
        }
    });

    // Collection
    Repeating_Meta.SectionsCollection = Backbone.Collection.extend({
        model: Repeating_Meta.Section,

        initialize: function() {
            this.on("change:state", this.saveStates, this);
        },
        toJSON: function() {
            var attrs = {};
            attrs.states = this.pluck('state');
            attrs.post_id = Sample_Repeating_Metabox.post_id;
            attrs.action = 'save_section_state';
            return attrs;
        },
        saveStates: function() {
            var self = this;
            $.ajax({
                type: "POST",
                url: Sample_Repeating_Metabox.ajaxurl,
                data: this.toJSON(),
                success: function(response) {}
            });
        }
    });

    // List View
    Repeating_Meta.sectionListView = Backbone.View.extend({

        events: {
            'update-sort': 'updateSort'
        },

        initialize: function() {
            this.listenTo(this.collection, 'add', this.render);
            this.listenTo(this.collection, 'remove', this.render);
        },

        appendModelView: function(model) {
            var el = new Repeating_Meta.sectionView({
                model: model
            }).render().el;
            this.$el.append(el);
        },

        render: function() {
            this.$el.children().remove();
            this.collection.each(this.appendModelView, this);
            this.collection.saveStates();
            return this;
        },

        updateSort: function(event, model) {
            this.collection.remove(model);

            this.collection.each(function(model, index) {
                var ordinal = index;
                if (index >= position) {
                    ordinal += 1;
                }
                model.set('ordinal', ordinal);
            });

            model.set('ordinal', position);
            this.collection.add(model, {
                at: position
            });

            this.render();

            //this.collection.saveStates();

        },

        stopSort: function(event, model) {

        }

    });

    // Singular View
    Repeating_Meta.sectionView = Backbone.View.extend({

        model: Repeating_Meta.Section,
        tagName: 'div',

        attributes: {
            class: "ui-state-default section"
        },

        // Get the template from the DOM
        template: _.template($(Sample_Repeating_Metabox.sectionTempl).html()),

        events: {
            'click .delete-section': 'removeSection',
            'click .toggle-section': 'toggleSection',
            'change input[type="text"]': 'updateInput',
            'change input[type="radio"]': 'updateRadio',
            'change textarea': 'updateText',
    //        'start-sort': 'stopTinyMCE',
            'reorder': 'reorder',
    //        'stop-sort': 'startTinyMCE'
        },

        initialize: function() {
            // set the toggle state
            this.$el.addClass(this.model.get('state'));
        },

        // Render the single input - include an index.
        render: function() {
            this.model.set('index', this.model.collection.indexOf(this.model));
            this.$el.html(this.template(this.model.attributes));

            this.setRadioState(this.model.get('index'));
    //        this.startTinyMCE(this.model.get('index'));
            return this;
        },

        // check the radio buttons
        setRadioState: function( index ) {

            var value = this.model.get('select');

            if (value) {
                this.$el.find('input[name="sections[' + index + '][select]"]').filter('[value="' + value + '"]').prop("checked", true);
            }

        },

        // tinyMCE
        startTinyMCE: function( index ) { console.log('start tinymce');
            tinyMCE.init({ selector : "#mceEditor-" + index });
        },

        stopTinyMCE: function(index) { console.log('stop tinymce');
        	tinyMCE.execCommand('mceRemoveEditor', false, '#mceEditor-' + index)
        },

        // toggle open/closed
        toggleSection: function(e) {
            e.preventDefault();

            var state = this.model.get('state');

            var index = Repeating_Meta.sectionCollection.indexOf(this.model) + 1;

            var _model = this.model;

            this.$el.find('.section-inside').slideToggle("slow", function() {

                $(e.currentTarget).closest('.section').toggleClass('open');

                if (state == 'open') {
                    $(e.currentTarget).removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
                    _model.set({
                        'state': 'closed'
                    });
                } else {
                    $(e.currentTarget).removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
                    _model.set({
                        'state': 'open'
                    });
                }

            });

        },

        // reorder after sort
        reorder: function(event, index) {
            this.$el.trigger('update-sort', [this.model, index]);
        },

        // remove/destroy a model
        removeSection: function(e) {
            e.preventDefault();
            if (confirm(Sample_Repeating_Metabox.confirm)) {
                this.model.destroy();
            }
        },

        // update text input value to model
        updateInput: function(e) {
            this.model.set({
                input: $(e.currentTarget).val()
            });
        },

        // update radio value to model
        updateRadio: function(e) {
            this.model.set({
                radio: $(e.currentTarget).val()
            });
        },

        // update textarea value to model
        updateText: function(e) {
            this.model.set({
                textarea: $(e.currentTarget).val()
            });
        }

    });

    // The Clone Button
    Repeating_Meta.CloneButton = Backbone.View.extend({

        // Attach events
        events: {
            'click': 'newSection'
        },

        // create new 
        newSection: function(e) {
            e.preventDefault();
            var newSection = new Repeating_Meta.Section({
                input: 'test',
                state: 'open'
            });
            Repeating_Meta.sectionCollection.add(newSection);
        },

    });

    // The Clear Button
    Repeating_Meta.ClearButton = Backbone.View.extend({

        // Attach events
        events: {
            'click': 'clearAll'
        },

        // clear all models 
        clearAll: function(e) {
            e.preventDefault();
            if (confirm(Sample_Repeating_Metabox.confirm)) {
                Repeating_Meta.sectionCollection.reset();
                Repeating_Meta.sectionList.render();
            }
        },

    });

    // init
    Repeating_Meta.init = function() {
        console.log(Sample_Repeating_Metabox.meta);
        
        Repeating_Meta.sectionCollection = new Repeating_Meta.SectionsCollection(Sample_Repeating_Metabox.meta);

        // Create the List View
        Repeating_Meta.sectionList = new Repeating_Meta.sectionListView({
            collection: Repeating_Meta.sectionCollection,
            el: Sample_Repeating_Metabox.sectionContainer
        });
        Repeating_Meta.sectionList.render();

        // Buttons
        Repeating_Meta.cloneButton = new Repeating_Meta.CloneButton({
            collection: Repeating_Meta.sectionCollection,
            el: Sample_Repeating_Metabox.cloneButton
        });
        Repeating_Meta.clearButton = new Repeating_Meta.ClearButton({
            collection: Repeating_Meta.sectionCollection,
            el: Sample_Repeating_Metabox.clearButton
        });

    };


    // Start when document is loaded
    $(document).ready(function() {
        Repeating_Meta.init();

        $(Sample_Repeating_Metabox.sectionContainer).sortable({
        	axis: 'y',
			opacity: 0.5,
			tolerance: 'pointer',
			handle: ".move-section",
            start: function(event, ui) { 
            	ui.item.trigger('start-sort', ui.item.index());
			},
			update: function(event, ui) {
                ui.item.trigger('reorder', ui.item.index());
            },
            stop: function(event, ui) {
				ui.item.trigger('stop-sort', ui.item.index());
			},
        });

    });

})(jQuery, Repeating_Meta);